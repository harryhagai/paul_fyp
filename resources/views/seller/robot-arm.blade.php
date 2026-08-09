@extends('layouts.dashboard')

@section('title', 'Robot Arm - Seller')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/seller-robot-arm.css') }}">
@endsection

@section('content')
    <div class="container-fluid mt-2 seller-robot-page"
        data-status-url="{{ route('seller.robot-arm.status') }}"
        data-pick-url="{{ route('seller.robot-arm.pick') }}"
        data-home-url="{{ route('seller.robot-arm.home') }}"
        data-stop-url="{{ route('seller.robot-arm.stop') }}"
        data-csrf="{{ csrf_token() }}">
        <div class="robot-page-header">
            <div>
                <h1 class="h3 mb-1 robot-page-title"><i class="bi bi-robot me-3"></i>Robot Arm Monitor</h1>
                <p class="robot-page-subtitle mb-0">Track ESP32 connection, live arm status, and pick-and-place commands.</p>
            </div>
            <div class="robot-header-actions">
                <button class="btn btn-sm btn-outline-primary themed-outline-btn" id="robotRefreshBtn">
                    <i class="bi bi-arrow-repeat me-1"></i>Refresh
                </button>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="robotAutoRefresh" checked>
                    <label class="form-check-label" for="robotAutoRefresh">Live</label>
                </div>
            </div>
        </div>

        @unless($robotConfigured)
            <div class="alert alert-warning robot-config-alert" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Robot arm is not configured. Set <strong>ROBOT_ARM_ENABLED=true</strong> and <strong>ROBOT_ARM_BASE_URL</strong> in your .env file.
            </div>
        @endunless

        <div class="robot-status-strip">
            <div class="robot-status-item">
                <span class="robot-status-label">Connection</span>
                <strong id="robotConnectionText">{{ $robotConfigured ? 'Checking...' : 'Not configured' }}</strong>
            </div>
            <div class="robot-status-item">
                <span class="robot-status-label">Arm Status</span>
                <strong id="robotStatusText">{{ $activeCommand->status ?? 'IDLE' }}</strong>
            </div>
            <div class="robot-status-item">
                <span class="robot-status-label">Endpoint</span>
                <strong class="robot-endpoint">{{ $robotBaseUrl ?: 'Not set' }}</strong>
            </div>
            <div class="robot-status-item">
                <span class="robot-status-label">Last Poll</span>
                <strong id="robotLastPoll">Waiting</strong>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-4">
                <div class="card robot-control-card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-command me-2"></i>Manual Override</h5>
                    </div>
                    <div class="card-body">
                        <form id="robotPickForm" class="robot-pick-form">
                            <div class="mb-3">
                                <label for="robotOrder" class="form-label">Order</label>
                                <select class="form-select" id="robotOrder" name="order_id" required>
                                    <option value="">Select order</option>
                                    @foreach($orders as $order)
                                        <option value="{{ $order['id'] }}" data-location="{{ $order['location'] }}">
                                            {{ $order['number'] }} - {{ $order['customer'] }} ({{ ucfirst($order['status']) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="robotLocation" class="form-label">Location</label>
                                <select class="form-select" id="robotLocation" name="location">
                                    <option value="">Use product location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}">LOCATION {{ $location }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-outline-primary themed-outline-btn db-primary-btn w-100" type="submit" id="robotPickBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                                <i class="bi bi-box-arrow-up-right me-2"></i>Send PICK
                            </button>
                        </form>

                        <div class="robot-quick-actions">
                            <button class="btn btn-outline-secondary themed-outline-btn" type="button" id="robotHomeBtn">
                                <i class="bi bi-house-up me-2"></i>HOME
                            </button>
                            <button class="btn btn-outline-danger" type="button" id="robotStopBtn">
                                <i class="bi bi-stop-circle me-2"></i>STOP
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Live Status</h5>
                        <span class="robot-status-pill" id="robotStatusPill">{{ $activeCommand->status ?? 'IDLE' }}</span>
                    </div>
                    <div class="card-body">
                        <div class="robot-process-panel" id="robotProcessPanel" aria-live="polite">
                            <div class="robot-process-overview">
                                <div class="robot-process-orbit" aria-hidden="true">
                                    <span class="robot-process-ring"></span>
                                    <span class="robot-process-icon"><i class="bi bi-robot"></i></span>
                                    <span class="robot-process-signal"></span>
                                </div>
                                <div class="robot-process-copy">
                                    <span class="robot-process-kicker">Automation workflow</span>
                                    <h3 id="robotProcessTitle">Ready for next command</h3>
                                    <p id="robotProcessMessage">The robot arm is standing by for a confirmed order.</p>
                                </div>
                            </div>

                            <div class="robot-process-track" aria-label="Robot command progress">
                                <div class="robot-process-line" aria-hidden="true">
                                    <span id="robotProcessFill"></span>
                                </div>
                                @foreach([
                                    ['status' => 'ACCEPTED', 'label' => 'Accepted', 'caption' => 'Command ready', 'icon' => 'bi-check2'],
                                    ['status' => 'MOVING', 'label' => 'Moving', 'caption' => 'Going to shelf', 'icon' => 'bi-arrow-right'],
                                    ['status' => 'PICKING', 'label' => 'Picking', 'caption' => 'Collecting item', 'icon' => 'bi-box-seam'],
                                    ['status' => 'PLACING', 'label' => 'Placing', 'caption' => 'On the conveyor', 'icon' => 'bi-inboxes'],
                                    ['status' => 'COMPLETED', 'label' => 'Complete', 'caption' => 'Order is ready', 'icon' => 'bi-check-lg'],
                                ] as $step)
                                    <div class="robot-process-step" data-step="{{ $step['status'] }}">
                                        <span class="robot-process-marker" aria-hidden="true">
                                            <i class="bi {{ $step['icon'] }}"></i>
                                        </span>
                                        <strong>{{ $step['label'] }}</strong>
                                        <small>{{ $step['caption'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="robot-command-summary">
                            <div>
                                <span>Active Command</span>
                                <strong id="activeCommandText">
                                    {{ $activeCommand ? $activeCommand->command . ($activeCommand->total > 1 ? ' ' . $activeCommand->sequence . '/' . $activeCommand->total : '') : 'None' }}
                                </strong>
                            </div>
                            <div>
                                <span>Order</span>
                                <strong id="activeOrderText">
                                    {{ $activeCommand?->order_reference ?? 'None' }}
                                </strong>
                            </div>
                            <div>
                                <span>Location</span>
                                <strong id="activeLocationText">
                                    {{ $activeCommand?->location ? 'LOCATION ' . $activeCommand->location : 'None' }}
                                </strong>
                            </div>
                            <div>
                                <span>Error</span>
                                <strong id="activeErrorText">{{ $activeCommand?->error ?? 'None' }}</strong>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Command History</h5>
                <small class="text-muted">Latest 12 commands</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle robot-command-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Command</th>
                                <th>Order</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody id="robotHistoryBody">
                            @forelse($recentCommands as $command)
                                <tr>
                                    <td>{{ $command->created_at?->format('M d, H:i:s') }}</td>
                                    <td>{{ $command->command }}{{ $command->total > 1 ? ' ' . $command->sequence . '/' . $command->total : '' }}</td>
                                    <td>{{ $command->order_reference ?? '-' }}</td>
                                    <td>{{ $command->location ? 'LOCATION ' . $command->location : '-' }}</td>
                                    <td><span class="robot-status-badge">{{ $command->status }}</span></td>
                                    <td>{{ $command->error ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No robot commands yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/seller-robot-arm.js') }}"></script>
@endpush
