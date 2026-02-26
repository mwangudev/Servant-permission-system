<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Taarifa ya Kuwa Nje ya Kituo</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            margin: 40px;
        }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .section-title {
            font-weight: bold;
            margin: 30px 0 15px;
            text-decoration: underline;
            font-size: 15px;
        }
        .signature-img {
            max-height: 60px;
            vertical-align: middle;
        }
        .mt-3 { margin-top: 20px; }
        .field-value {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>

@php
    $user = $leaveRequest->user;
    $department = $user?->department;

    $fullName = $user
        ? trim($user->fname.' '.$user->mname.' '.$user->lname)
        : null;

    $days = ($leaveRequest->start_date && $leaveRequest->end_date)
        ? \Carbon\Carbon::parse($leaveRequest->start_date)
            ->diffInDays(\Carbon\Carbon::parse($leaveRequest->end_date)) + 1
        : null;

    $startDate = $leaveRequest->start_date
        ? \Carbon\Carbon::parse($leaveRequest->start_date)->format('d/m/Y')
        : null;

    $endDate = $leaveRequest->end_date
        ? \Carbon\Carbon::parse($leaveRequest->end_date)->format('d/m/Y')
        : null;

    $createdDate = $leaveRequest->created_at
        ? $leaveRequest->created_at->format('d/m/Y')
        : null;

    $hodSignedDate = $leaveRequest->hod_signed_at
        ? \Carbon\Carbon::parse($leaveRequest->hod_signed_at)->format('d/m/Y')
        : null;

    $adminSignedDate = $leaveRequest->admin_signed_at
        ? \Carbon\Carbon::parse($leaveRequest->admin_signed_at)->format('d/m/Y')
        : null;

    /* ===========================
       BASE64 SIGNATURES
    ============================*/

    $hodBase64 = null;
    if ($leaveRequest->hod_signature) {
        $hodPath = public_path($leaveRequest->hod_signature);
        if (file_exists($hodPath)) {
            $type = pathinfo($hodPath, PATHINFO_EXTENSION);
            $data = file_get_contents($hodPath);
            $hodBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
        }
    }

    $adminBase64 = null;
    if ($leaveRequest->admin_signature) {
        $adminPath = public_path($leaveRequest->admin_signature);
        if (file_exists($adminPath)) {
            $type = pathinfo($adminPath, PATHINFO_EXTENSION);
            $data = file_get_contents($adminPath);
            $adminBase64 = 'data:image/'.$type.';base64,'.base64_encode($data);
        }
    }
@endphp


<h3 class="text-center bold">WIZARA YA AFYA</h3>
<h4 class="text-center bold" style="text-decoration: underline;">
    TAARIFA YA KUWA NJE YA KITUO
</h4>


<div class="section-title">
    SEHEMU A: (IJAZWE NA MTUMISHI ANAYEOMBA RUHUSA)
</div>

<div class="mt-3">
    1. (a) Mimi
    <span class="field-value">{{ $fullName ?? '......................' }}</span>
    wa Idara/Kitengo
    <span class="field-value">{{ $department?->name ?? '......................' }}</span>
    Cheo
    <span class="field-value">{{ $user?->assigned_as ?? '......................' }}</span>
    naomba ruhusa ya kuwa nje ya kituo cha kazi kwa siku
    <span class="field-value">{{ $days ?? '..........' }}</span>
    kuanzia tarehe
    <span class="field-value">{{ $startDate ?? '......................' }}</span>
    hadi tarehe
    <span class="field-value">{{ $endDate ?? '......................' }}</span>.
</div>

<div class="mt-3">
    (b) Sababu ya kuwa nje ya kituo cha kazi:
    <span class="field-value">
        {{ $leaveRequest->reasons ?? '.............................................................................' }}
    </span>
</div>

<div class="mt-3">
    (c) Ruhusa ya mwisho nilipata tarehe
    <span class="field-value">
        {{ $lastLeave?->start_date
            ? \Carbon\Carbon::parse($lastLeave->start_date)->format('d/m/Y')
            : '.....................' }}
    </span>
    mpaka tarehe
    <span class="field-value">
        {{ $lastLeave?->end_date
            ? \Carbon\Carbon::parse($lastLeave->end_date)->format('d/m/Y')
            : '......................' }}
    </span>
    ilikuwa ruhusa ya
    <span class="field-value">
        {{ $lastLeave?->request_type
            ? ucfirst($lastLeave->request_category).'('.ucfirst($lastLeave->request_type).')'
            : '.....................' }}
    </span>.
</div>

<div class="mt-3">
    Sahihi
    <span class="field-value">........................</span>
    Cheo
    <span class="field-value">{{ $user?->assigned_as ?? '........................' }}</span>
    Tarehe
    <span class="field-value">{{ $createdDate ?? '........................' }}</span>
</div>


<div class="section-title">
    SEHEMU B: (IJAZWE NA MKUU WA IDARA/KITENGO)
</div>

<div class="mt-3">
    2.(a) Siidhinishi/Naidhinisha ombi kwa sababu zifuatazo: <br>
    <span class="field-value">
        {{ $leaveRequest->hod_remarks ?? '.............................................................................' }}
    </span>
</div>

<div class="mt-3">
    Sahihi
    @if($hodBase64)
        <img src="{{ $hodBase64 }}" class="signature-img">
    @else
        <span class="field-value">........................</span>
    @endif

    Cheo <span class="field-value">Mkuu wa Idara</span>
    Tarehe
    <span class="field-value">{{ $hodSignedDate ?? '........................' }}</span>
</div>


<div class="section-title">
    SEHEMU C: (IDHINI YA AFISA MWAJIRI)
</div>

<div class="mt-3">
    @if($leaveRequest->status === 'approved')
        Naidhinisha ombi kwa sababu zifuatazo:
        <span class="field-value">
            {{ $leaveRequest->admin_remarks ?? '.............................................................................' }}
        </span>
    @elseif($leaveRequest->status === 'rejected')
        Siidhinishi ombi kwa sababu zifuatazo: <br>
        <span class="field-value">
            {{ $leaveRequest->admin_remarks ?? '.............................................................................' }}
        </span>
    @endif
</div>

<div class="mt-3">
    Sahihi
    @if($adminBase64)
        <img src="{{ $adminBase64 }}" class="signature-img">
    @else
        <span class="field-value">........................</span>
    @endif

    Cheo <span class="field-value">Afisa Mwajiri</span>
    Tarehe
    <span class="field-value">{{ $adminSignedDate ?? '........................' }}</span>
</div>

</body>
</html>
