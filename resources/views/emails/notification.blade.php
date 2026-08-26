<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #333;">{{ $notification->title }}</h2>
        <small style="color: #666;">{{ $notification->created_at->format('M d, Y H:i') }}</small>
    </div>

    <div style="padding: 0 20px; margin-bottom: 20px;">
        <p style="color: #555; line-height: 1.6;">{{ $notification->message }}</p>

        @if($notification->action_url)
            <div style="margin-top: 20px;">
                <a href="{{ $notification->action_url }}" style="display: inline-block; background-color: #0d6efd; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none;">
                    View Details
                </a>
            </div>
        @endif
    </div>

    <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

    <div style="padding: 0 20px; color: #999; font-size: 12px;">
        <p>You received this notification because you have alerts enabled for {{ $notification->category }}.</p>
        <p>
            <a href="{{ route('notifications.preferences') }}" style="color: #0d6efd;">Manage your notification preferences</a>
        </p>
    </div>
</div>