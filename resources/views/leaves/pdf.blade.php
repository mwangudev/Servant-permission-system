<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request - {{ $leaveRequest->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', 'Helvetica', sans-serif;
            font-size: 12pt;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            width: 90%;
            margin: 30px auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 22pt;
            color: #007bff;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 11pt;
            color: #555;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }
        .info-table th {
            background-color: #f8f9fa;
            width: 30%;
            font-weight: bold;
        }
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 25px 0 10px;
            color: #333;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 45%;
        }
        .signature img {
            max-width: 180px;
            max-height: 80px;
            margin-bottom: 8px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 80%;
            margin: 10px auto 5px;
        }
        .footer {
            text-align: center;
            margin-top: 60px;
            font-size: 10pt;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }
        @page {
            margin: 1.5cm 1.2cm;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>LEAVE REQUEST APPROVAL</h1>
        <p>Official Document • Reference No: LR-{{ str_pad($leaveRequest->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <table class="info-table">
        <tr>
            <th>Employee Name</th>
            <td>{{ $leaveRequest->user->full_name ?? $leaveRequest->user->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Employee ID / Email</th>
            <td>{{ $leaveRequest->user->employee_id ?? '—' }} • {{ $leaveRequest->user->email }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $leaveRequest->user->department->name ?? 'Not Assigned' }}</td>
        </tr>
        <tr>
            <th>Leave Type</th>
            <td>{{ ucfirst(str_replace('_', ' ', $leaveRequest->request_type)) }}</td>
        </tr>
        <tr>
            <th>Duration</th>
            <td>
                From: {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M Y') }}<br>
                To: {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M Y') }}<br>
                <strong>Total Days:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->diffInDays(\Carbon\Carbon::parse($leaveRequest->end_date)) + 1 }}
            </td>
        </tr>
        <tr>
            <th>Reason / Destination</th>
            <td>{{ $leaveRequest->reasons ?? 'No reason provided' }} {!! $leaveRequest->destination ? '<br><strong>Destination:</strong> ' . $leaveRequest->destination : '' !!}</td>
        </tr>
        <tr>
            <th>Submitted On</th>
            <td>{{ \Carbon\Carbon::parse($leaveRequest->created_at)->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                <strong style="color: #28a745;">APPROVED</strong><br>
                Finalized on: {{ \Carbon\Carbon::parse($leaveRequest->admin_signed_at ?? $leaveRequest->updated_at)->format('d M Y') }}
            </td>
        </tr>
    </table>

    <div class="section-title">Supporting Document / Remarks</div>
    <p style="line-height: 1.6;">
        {{ $leaveRequest->hod_remarks ?? 'No HOD remarks.' }}<br><br>
        {{ $leaveRequest->admin_remarks ?? 'No Admin remarks.' }}
    </p>

    @if($leaveRequest->report_path)
        <p><em>Supporting document uploaded: Yes (viewable in system)</em></p>
    @endif

    <div class="signature-area">
        <div class="signature">
            <strong>HOD / Supervisor</strong><br>
            @if($leaveRequest->hod_signature)
                <img src="{{ public_path('storage/' . $leaveRequest->hod_signature) }}" alt="HOD Signature">
            @else
                <div style="height: 60px;"></div>
            @endif
            <div class="signature-line"></div>
            <p>{{ $leaveRequest->user->department->hod->name ?? 'Head of Department' }}<br>Date: {{ $leaveRequest->hod_signed_at ? \Carbon\Carbon::parse($leaveRequest->hod_signed_at)->format('d M Y') : '—' }}</p>
        </div>

        <div class="signature">
            <strong>Admin / Authorized Officer</strong><br>
            @if($leaveRequest->admin_signature)
                <img src="{{ public_path('storage/' . $leaveRequest->admin_signature) }}" alt="Admin Signature">
            @else
                <div style="height: 60px;"></div>
            @endif
            <div class="signature-line"></div>
            <p>Administration Department<br>Date: {{ $leaveRequest->admin_signed_at ? \Carbon\Carbon::parse($leaveRequest->admin_signed_at)->format('d M Y') : '—' }}</p>
        </div>
    </div>

    <div class="footer">
        This is an officially approved leave request generated by the system.<br>
        For verification, contact HR/Admin. • Confidential • {{ now()->format('Y') }}
    </div>

</div>

</body>
</html>
