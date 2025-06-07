<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ticket #{{ $ticket->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 0 auto;
        }

        h1 {
            font-size: 20px;
            color: #222;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .ticket-header {
            margin-bottom: 25px;
        }

        .ticket-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .ticket-header th,
        .ticket-header td {
            padding: 8px;
            text-align: left;
        }

        .ticket-header th {
            width: 120px;
            background-color: #f7f7f7;
            font-weight: bold;
        }

        .ticket-section {
            margin-bottom: 25px;
        }

        .ticket-section h2 {
            font-size: 16px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .description,
        .reply {
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 5px;
            white-space: pre-wrap;
            /* Preserve line breaks and spaces */
            word-wrap: break-word;
            /* Wrap long words */
        }

        .reply {
            margin-bottom: 15px;
        }

        .reply-header {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .reply-header span {
            font-weight: normal;
            color: #777;
            font-size: 11px;
        }

        .internal-note {
            background-color: #fffbe6;
            border-color: #ffe58f;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Support Ticket #{{ $ticket->id }} - {{ e($ticket->title) }}</h1>

        <div class="ticket-header">
            <table>
                <tr>
                    <th>Status:</th>
                    <td>{{ $ticket->status->name }}</td>
                    <th>Priority:</th>
                    <td>{{ ucfirst($ticket->priority) }}</td>
                </tr>
                <tr>
                    <th>Client:</th>
                    <td>{{ $ticket->user->name }} ({{ $ticket->user->email }})</td>
                    <th>Category:</th>
                    <td>{{ $ticket->category->name }}</td>
                </tr>
                <tr>
                    <th>Assigned Agent:</th>
                    <td>{{ $ticket->agent->name ?? 'Not Assigned' }}</td>
                    <th>Created At:</th>
                    <td>{{ $ticket->created_at->format('M d, Y H:i A') }}</td>
                </tr>
                <tr>
                    <th>Last Updated:</th>
                    <td>{{ $ticket->updated_at->format('M d, Y H:i A') }}</td>
                    <th></th>
                    <td></td>
                </tr>
            </table>
        </div>

        <div class="ticket-section">
            <h2>Initial Description</h2>
            <div class="description">{{ e($ticket->description) }}</div>
        </div>

        @if($ticket->replies->isNotEmpty())
        <div class="ticket-section">
            <h2>Conversation History</h2>
            @foreach($ticket->replies as $reply)
            <div class="reply @if($reply->is_internal) internal-note @endif">
                <div class="reply-header">
                    {{ $reply->user->name }}
                    @if($reply->is_internal)
                    <span>(Internal Note)</span>
                    @elseif($reply->user->role !== 'client')
                    <span>({{ ucfirst($reply->user->role) }})</span>
                    @endif
                    <span>- {{ $reply->created_at->format('M d, Y H:i A') }}</span>
                </div>
                <div class="reply-body">
                    {{ e($reply->body) }}
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="footer">
            Generated on {{ now()->format('M d, Y H:i A') }} by {{ config('app.name') }}
        </div>
    </div>
</body>

</html>