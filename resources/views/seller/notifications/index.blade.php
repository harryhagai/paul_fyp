@extends('layouts.dashboard')

@section('title', 'Notifications - KidsStore Seller')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/seller-notifications.css') }}">
@endsection

@section('content')
<div class="container-fluid seller-notifications-page"
     id="sellerNotificationsPage"
     data-mark-all-url="{{ route('seller.notifications.markAllAsRead') }}"
     data-base-url="{{ url('seller/notifications') }}"
     data-csrf="{{ csrf_token() }}">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 notifications-page-title"><i class="bi bi-bell me-3"></i>Notifications</h1>
                    <p class="notifications-page-subtitle mb-0">Select a notification from the right panel to view full details.</p>
                </div>
                @if($unreadCount > 0)
                    <button class="btn btn-outline-primary themed-outline-btn" id="markAllBtn">
                        <i class="bi bi-check-all me-2"></i>Mark All as Read
                    </button>
                @endif
            </div>

            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card notification-detail-card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-chat-square-text me-2"></i>Message Details</h5>
                        </div>
                        <div class="card-body" id="notificationDetailPanel">
                            @if($notifications->count() > 0)
                                @php
                                    $first = $notifications->first();
                                    $firstNotificationKey = $first->public_id ?: $first->id;
                                @endphp
                                <div class="notification-detail-wrap" data-current-id="{{ $firstNotificationKey }}">
                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                        <h5 class="notification-title mb-0">{{ $first->title }}</h5>
                                        @if(!$first->read_at)
                                            <span class="badge notification-badge-new">New</span>
                                        @endif
                                        <span class="badge notification-badge-priority priority-{{ $first->priority }}">{{ ucfirst($first->priority) }}</span>
                                    </div>
                                    <div class="notification-detail-meta mb-3">
                                        <small class="text-muted d-block"><i class="bi bi-clock me-1"></i>{{ $first->created_at->diffForHumans() }}</small>
                                        @if($first->expires_at)
                                            <small class="text-muted d-block"><i class="bi bi-calendar-x me-1"></i>Expires {{ $first->expires_at->diffForHumans() }}</small>
                                        @endif
                                    </div>
                                    <div class="notification-detail-message mb-3">{{ $first->message }}</div>
                                    <div class="d-flex flex-wrap gap-2" id="notificationDetailActions">
                                        @if($first->action_url)
                                            <a href="{{ $first->action_url }}" class="btn btn-sm btn-outline-primary themed-outline-btn" id="detailActionBtn"><i class="bi bi-link-45deg me-1"></i>Action</a>
                                        @endif
                                        @if(!$first->read_at)
                                            <button class="btn btn-sm btn-outline-primary themed-outline-btn" id="detailMarkReadBtn" data-id="{{ $firstNotificationKey }}"><i class="bi bi-check2 me-1"></i>Mark Read</button>
                                        @endif
                                        <button class="btn btn-sm btn-outline-danger" id="detailDeleteBtn" data-id="{{ $firstNotificationKey }}"><i class="bi bi-trash me-1"></i>Delete</button>
                                    </div>
                                </div>
                            @else
                                <div class="notifications-empty-state text-center mx-auto">
                                    <div class="notifications-empty-icon-wrap"><i class="bi bi-bell-slash notifications-empty-icon"></i></div>
                                    <h6 class="notifications-empty-title mb-1">No notifications yet</h6>
                                    <p class="notifications-empty-text mb-0">You will see notifications here when there are new activities.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card notifications-list-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Notifications List</h5>
                            <span class="badge bg-secondary">{{ $notifications->total() }}</span>
                        </div>
                        <div class="card-body notifications-list-wrap">
                            @if($notifications->count() > 0)
                                @foreach($notifications as $notification)
                                    @php $notificationKey = $notification->public_id ?: $notification->id; @endphp
                                    <button type="button"
                                            class="notification-list-item {{ $loop->first ? 'active' : '' }} {{ $notification->read_at ? 'is-read' : 'is-unread' }}"
                                            data-id="{{ $notificationKey }}"
                                            data-title="{{ e($notification->title) }}"
                                            data-message="{{ e($notification->message) }}"
                                            data-priority="{{ $notification->priority }}"
                                            data-created="{{ $notification->created_at->diffForHumans() }}"
                                            data-expires="{{ $notification->expires_at ? $notification->expires_at->diffForHumans() : '' }}"
                                            data-action-url="{{ $notification->action_url ?? '' }}"
                                            data-read="{{ $notification->read_at ? '1' : '0' }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="notification-list-title">{{ Str::limit($notification->title, 46) }}</div>
                                                <div class="notification-list-text">{{ Str::limit($notification->message, 70) }}</div>
                                            </div>
                                            @if(!$notification->read_at)
                                                <span class="notification-dot"></span>
                                            @endif
                                        </div>
                                        <div class="notification-list-time">{{ $notification->created_at->diffForHumans() }}</div>
                                    </button>
                                @endforeach
                            @else
                                <div class="notifications-empty-state text-center mx-auto">
                                    <div class="notifications-empty-icon-wrap"><i class="bi bi-bell-slash notifications-empty-icon"></i></div>
                                    <h6 class="notifications-empty-title mb-1">No notifications</h6>
                                    <p class="notifications-empty-text mb-0">Nothing to show right now.</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-center">
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/seller-notifications.js') }}"></script>
@endpush
