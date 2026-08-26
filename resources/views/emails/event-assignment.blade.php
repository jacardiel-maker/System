<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Assignment</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { background: #d97706; color: white; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .event-details { background: #fef3c7; border-left: 4px solid #d97706; padding: 15px; margin: 20px 0; border-radius: 4px; }
        .event-details p { margin: 8px 0; color: #374151; }
        .btn { display: inline-block; background: #d97706; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍽️ Steph's Catering by Chef Nono</h1>
            <p>New Event Assignment</p>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $waiterName }}</strong>,</p>
            
            <p>You have been assigned to a new catering event. Here are the details:</p>
            
            <div class="event-details">
                <p><strong>📅 Event:</strong> {{ $eventName }}</p>
                <p><strong>📆 Date:</strong> {{ $eventDate }}</p>
                <p><strong>⏰ Event Time:</strong> {{ $eventTime }}</p>
                <p><strong>🔧 Setup Time:</strong> {{ $setupTime }}</p>
                <p><strong>📍 Venue:</strong> {{ $venue }}</p>
            </div>
            
            <p>Please confirm your participation as soon as possible.</p>
            
            <center>
                <a href="{{ $eventUrl }}" class="btn">✅ View & Confirm Event</a>
            </center>
            
            <p>If you have any questions or conflicts, please contact the admin immediately.</p>
            
            <p>Best regards,<br>
            <strong>Steph's Catering by Chef Nono Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This email was sent from the Steph's Catering Management System.</p>
            <p>© 2026 Steph's Catering by Chef Nono. All rights reserved.</p>
        </div>
    </div>
</body>
</html>