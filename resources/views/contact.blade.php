@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/contact.css') }}">
@endsection

@section('content')
@php
    $settings = $pageSettings ?? [];
@endphp

<div class="contact-page">
    <section class="contact-hero-section">
        <div class="contact-shell text-center">
            <h1 class="contact-hero-title">{{ $pageContent->title ?? 'Contact Us' }}</h1>
            <p class="contact-hero-subtitle">
                {{ $pageContent->subtitle ?? "We'd love to hear from you! Get in touch with our team for any questions, support, or feedback." }}
            </p>
        </div>
    </section>

    <section class="contact-section">
        <div class="contact-shell">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="contact-card contact-form-card">
                        <h2 class="contact-section-title">{{ $settings['form_title'] ?? 'Send us a Message' }}</h2>

                        <form action="#" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" id="subject" name="subject" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn contact-primary-btn">
                                        <i class="bi bi-send-fill me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="contact-info">
                        <div class="contact-card mb-4">
                            <h3 class="contact-card-title">{{ $settings['info_title'] ?? 'Get in Touch' }}</h3>

                            <div class="contact-item">
                                <div class="contact-icon"><i class="bi bi-envelope-fill"></i></div>
                                <div>
                                    <div class="contact-item-label">Email</div>
                                    <div class="contact-item-value">{{ $settings['email'] ?? 'support@kidzstore365.com' }}</div>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="contact-icon"><i class="bi bi-telephone-fill"></i></div>
                                <div>
                                    <div class="contact-item-label">Phone</div>
                                    <div class="contact-item-value">{{ $settings['phone'] ?? '+255 123 456 789' }}</div>
                                </div>
                            </div>

                            <div class="contact-item mb-0">
                                <div class="contact-icon"><i class="bi bi-geo-alt-fill"></i></div>
                                <div>
                                    <div class="contact-item-label">Address</div>
                                    <div class="contact-item-value">{{ $settings['address'] ?? 'Dar es Salaam, Tanzania' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="contact-card">
                            <h3 class="contact-card-title">{{ $settings['hours_title'] ?? 'Business Hours' }}</h3>

                            <div class="hours-item">
                                <span>{{ $settings['weekday_label'] ?? 'Monday - Friday' }}</span>
                                <span>{{ $settings['weekday_hours'] ?? '9:00 AM - 6:00 PM' }}</span>
                            </div>

                            <div class="hours-item">
                                <span>{{ $settings['saturday_label'] ?? 'Saturday' }}</span>
                                <span>{{ $settings['saturday_hours'] ?? '10:00 AM - 4:00 PM' }}</span>
                            </div>

                            <div class="hours-item mb-0 pb-0 border-0">
                                <span>{{ $settings['sunday_label'] ?? 'Sunday' }}</span>
                                <span>{{ $settings['sunday_hours'] ?? 'Closed' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
