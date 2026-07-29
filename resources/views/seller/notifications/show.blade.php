@extends('layouts.dashboard')

@section('title', 'Notification - KidsStore Seller')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('seller.notifications.index') }}">Notifications</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $notification->title }}</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $notification->title }}</h4>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> {{ $notification->created_at->format('M d, Y H:i') }}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="notification-content">
                                <p class="lead">{{ $notification->message }}</p>

                                @if($notification->data && count($notification->data) > 0)
                                    <div class="mt-4">
                                        <h6>Additional Details:</h6>
                                        <div class="bg-light p-3 rounded">
                                            @foreach($notification->data as $key => $value)
                                                <div class="mb-2">
                                                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="bi bi-info-circle text-info"></i> Notification Info
                                    </h6>
                                    <div class="mb-2">
                                        <strong>Type:</strong>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Priority:</strong>
                                        <span class="badge bg-{{ $notification->priority === 'high' ? 'danger' : ($notification->priority === 'medium' ? 'warning' : 'secondary') }}">{{ ucfirst($notification->priority) }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Status:</strong>
                                        @if($notification->read_at)
                                            <span class="badge bg-success">Read</span>
                                        @else
                                            <span class="badge bg-warning">Unread</span>
                                        @endif
                                    </div>
                                    @if($notification->read_at)
                                        <div class="mb-2">
                                            <strong>Read at:</strong>
                                            {{ $notification->read_at->format('M d, Y H:i') }}
                                        </div>
                                    @endif
                                    <div class="mb-2">
                                        <strong>Created:</strong>
                                        {{ $notification->created_at->format('M d, Y H:i') }}
                                    </div>
                                    @if($notification->expires_at)
                                        <div class="mb-2">
                                            <strong>Expires:</strong>
                                            {{ $notification->expires_at->format('M d, Y H:i') }} ({{ $notification->expires_at->diffForHumans() }})
                                        </div>
                                    @endif
                                    @if($notification->action_url)
                                        <div class="mb-2">
                                            <strong>Action:</strong>
                                            <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-info">Go to Action</a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="btn-group-vertical w-100" role="group">
                                    <a href="{{ route('seller.notifications.index') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Notifications
                                    </a>
                                    @if(!$notification->read_at)
                                        <button class="btn btn-outline-success" onclick="markAsRead()">
                                            <i class="bi bi-check"></i> Mark as Read
                                        </button>
                                    @endif
                                    <button class="btn btn-outline-danger" onclick="deleteNotification()">
                                        <i class="bi bi-trash"></i> Delete Notification
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function markAsRead() {
    fetch(`{{ route('seller.notifications.markAsRead', $notification->public_id) }}`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You won\'t be able to recover this notification!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route('seller.notifications.destroy', $notification->public_id) }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(() => {
                Swal.fire(
                    'Deleted!',
                    'The notification has been deleted.',
                    'success'
                ).then(() => {
                    window.location.href = '{{ route('seller.notifications.index') }}';
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire(
                    'Error!',
                    'Something went wrong. Please try again.',
                    'error'
                );
            });
        }
    });
}
</script>
@endsection
