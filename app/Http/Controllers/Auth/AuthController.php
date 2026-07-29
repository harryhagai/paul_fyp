<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PendingShopAction;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm(Request $request, PendingShopAction $pendingShopAction)
    {
        if (!$pendingShopAction->capture($request)) {
            $pendingShopAction->rememberIntendedUrl($request);
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request, PendingShopAction $pendingShopAction)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = $credentials['email'];
        $cacheKey = 'login_attempts_' . md5($email);
        $lockoutKey = 'login_lockout_' . md5($email);

        // Check if account is locked
        if (Cache::has($lockoutKey)) {
            $lockoutTime = Cache::get($lockoutKey);
            $remainingTime = Carbon::now()->diffInMinutes(Carbon::parse($lockoutTime), false);

            if ($remainingTime > 0) {
                return back()->withErrors([
                    'email' => "Too many failed login attempts. Please try again in {$remainingTime} minute(s).",
                ])->onlyInput('email');
            } else {
                // Lockout period has expired, remove the lock
                Cache::forget($lockoutKey);
                Cache::forget($cacheKey);
            }
        }

        Log::info('Attempting login for email: ' . $email);

        $user = User::query()->where('email', $email)->first();
        if ($user) {
            Log::info('User exists with ID: ' . $user->id . ', role: ' . $user->role);
            $passwordMatches = Hash::check($credentials['password'], $user->password);
            Log::info('Password matches: ' . ($passwordMatches ? 'yes' : 'no'));
        } else {
            Log::info('User does not exist');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::info('Login successful for email: ' . $email);

            // Clear failed attempts on successful login
            Cache::forget($cacheKey);
            Cache::forget($lockoutKey);

            $request->session()->regenerate();

            $user = Auth::user();
            Log::info('User role: ' . $user->role);

            $allowedRoles = ['admin', 'customer', 'seller'];
            if (!in_array($user->role, $allowedRoles, true)) {
                Log::warning('Blocked login due to unsupported role', [
                    'user_id' => $user->id,
                    'role' => $user->role,
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your account role is not authorized. Please contact support.',
                ]);
            }

            $fallbackRoute = match ($user->role) {
                'admin'    => redirect()->route('admin.dashboard'),
                'customer' => redirect()->route('customer.dashboard'),
                'seller'   => redirect()->route('seller.dashboard'),
                default    => redirect()->route('login'),
            };

            if ($user->role === 'customer') {
                if ($pendingShopAction->has($request) && !$user->hasVerifiedEmail()) {
                    return redirect()->route('verification.notice')->with(
                        'status',
                        'Please verify your email address to complete your requested shop action.'
                    );
                }

                $pendingResult = $pendingShopAction->process($request);
                if ($pendingResult) {
                    $redirectResponse = redirect()->to($pendingResult['redirect_url']);
                    if ($pendingResult['flash_type'] && $pendingResult['message']) {
                        $redirectResponse->with($pendingResult['flash_type'], $pendingResult['message']);
                    }
                } else {
                    $fallbackUrl = $fallbackRoute->getTargetUrl();
                    $redirectResponse = redirect()->intended($fallbackUrl);
                }
            } else {
                $request->session()->forget(PendingShopAction::SESSION_KEY);
                $request->session()->forget('url.intended');
                $redirectResponse = $fallbackRoute;
            }

            return $redirectResponse;
        }

        // Failed login attempt
        $attempts = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $attempts, now()->addMinutes(30)); // Store attempts for 30 minutes

        if ($attempts >= 3) {
            // Lock the account for 30 minutes
            Cache::put($lockoutKey, now()->addMinutes(30), now()->addMinutes(30));
            return back()->withErrors([
                'email' => 'Too many failed login attempts. Your account has been locked for 30 minutes.',
            ])->onlyInput('email');
        }

        $remainingAttempts = 3 - $attempts;
        return back()->withErrors([
            'email' => "Invalid email or password. {$remainingAttempts} attempt(s) remaining.",
        ])->onlyInput('email');
    }

    /**
     * Show registration form
     */
    public function showRegistrationForm(Request $request, PendingShopAction $pendingShopAction)
    {
        $pendingShopAction->capture($request);

        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $normalizedPhone = preg_replace('/\D+/', '', (string) $request->input('phone_number'));
        $request->merge(['phone_number' => $normalizedPhone]);

        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'min:10', 'max:20', 'regex:/^\d+$/', 'unique:users,phone_number'],
            'password'     => ['required', PasswordRule::min(8)->letters()->numbers()],
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'password'     => Hash::make($validated['password']),
            'role'         => 'customer', // default role
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('status', 'We sent a verification link to your email. Please verify your email address.');
    }

    public function showVerificationNotice(Request $request, PendingShopAction $pendingShopAction)
    {
        $pendingShopAction->capture($request);

        return view('auth.verify-email');
    }

    public function verifyEmail(
        EmailVerificationRequest $request,
        PendingShopAction $pendingShopAction
    ) {
        $request->fulfill();

        if ($request->user()->role === 'customer') {
            $pendingResult = $pendingShopAction->process($request);
            if ($pendingResult) {
                $response = redirect()->to($pendingResult['redirect_url']);
                if ($pendingResult['flash_type'] && $pendingResult['message']) {
                    $response->with($pendingResult['flash_type'], $pendingResult['message']);
                }

                return $response;
            }
        }

        return redirect()->route('customer.dashboard')
            ->with('success', 'Email verified successfully.');
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been successfully logged out.');
    }

}
