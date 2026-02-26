<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Taarifa ya Kuwa Nje ya Kituo</title>
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; font-size: 14px; line-height: 1.6; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .section-title { font-weight: bold; margin-top: 20px; text-decoration: underline; }
        .dotted-line { border-bottom: 1px dotted #000; display: inline-block; min-width: 150px; }
        .signature-img { max-height: 40px; margin-bottom: -10px; }
        .mt-3 { margin-top: 15px; }
    </style>
</head>
<body>

    <h3 class="text-center bold">WIZARA YA AFYA</h3>
    <h4 class="text-center bold" style="text-decoration: underline;">TAARIFA YA KUWA NJE YA KITUO</h4>

    <div class="section-title">SEHEMU A: (IJAZWE NA MTUMISHI ANAYEOMBA RUHUSA)</div>
    <div class="mt-3">
        1. (a) Mimi <span class="dotted-line">{{ $leaveRequest->user->fname ?? '..................................' }}</span>
        wa Idara/Kitengo <span class="dotted-line">{{ $leaveRequest->user->department->name ?? '..................................' }}</span>
        Cheo <span class="dotted-line">{{ $leaveRequest->user->role ?? '..................................' }}</span>
        naomba ruhusa ya kuwa nje ya kituo cha kazi kikazi/shughuli binafsi kwa siku <span class="dotted-line">{{ $leaveRequest->duration ?? '..........' }}</span>
        kuanzia tarehe <span class="dotted-line">{{ $leaveRequest->start_date ?? '........................' }}</span>
        hadi tarehe <span class="dotted-line">{{ $leaveRequest->end_date ?? '........................' }}</span>.
    </div>

    <div class="mt-3">
        (b) Taja sababu ya kuwa nje ya kituo cha kazi: <br>
        <span class="dotted-line" style="width: 100%;">{{ $leaveRequest->reasons ?? '............................................................................................................' }}</span>
    </div>

    <div class="mt-3">
        (c) Ruhusa ya mwisho nilipata tarehe
        <span class="dotted-line">
            {{ $lastLeave ? \Carbon\Carbon::parse($lastLeave->start_date)->format('Y/m/d') : 'Hakuna' }}
        </span>
        mpaka tarehe
        <span class="dotted-line">
            {{ $lastLeave ? \Carbon\Carbon::parse($lastLeave->end_date)->format('Y/m/d') : 'Hakuna' }}
        </span>
        ilikuwa ruhusa ya
        <span class="dotted-line">
            {{ $lastLeave ? ucfirst($lastLeave->request_type) : 'Hakuna' }}
        </span>.
        Kama ni safari ya kikazi eleza kazi uliyoenda kufanya na ambatanisha nakala ya taarifa ya safari husika uliyowasilisha kwa mamlaka iliyokutuma.
    </div>

    <div class="mt-3">
        Sahihi <span class="dotted-line">........................</span>
        Cheo <span class="dotted-line">........................</span>
        Tarehe <span class="dotted-line">{{ $leaveRequest->created_at->format('d/m/Y') }}</span>
    </div>

    <div class="section-title">SEHEMU B: (IJAZWE NA MKUU WA IDARA/KITENGO)</div>
    <div class="mt-3">
        2.(a) Siidhinishi/Naidhinisha ombi kwa sababu zifuatazo: <br>
        <span class="dotted-line" style="width: 100%;">{{ $leaveRequest->hod_remarks ?? '............................................................................................................' }}</span>
    </div>

    <div class="mt-3">
        (b) Nathibitisha kuwa mhusika amewasilisha na kuambatanisha taarifa ya safari na utekelezaji wa maagizo/hoja zilizojitokeza kwa safari aliyoenda mara ya mwisho.
    </div>

    <div class="mt-3">
        Sahihi
        @if($leaveRequest->hod_signature)
            <img src="{{ public_path($leaveRequest->hod_signature) }}" class="signature-img">
        @else
            <span class="dotted-line">........................</span>
        @endif
        Cheo <span class="dotted-line">Mkuu wa Idara</span>
        Tarehe <span class="dotted-line">{{ $leaveRequest->hod_signed_at ?? '........................' }}</span>
    </div>

    <div class="section-title">SEHEMU C: (IDHINI YA AFISA MWAJIRI/AFISA MWANDAMIZI ALIYEKASIMIWA MAMLAKA)</div>
    <div class="mt-3">
        a) Siidhinishi/Naidhinisha ombi kwa sababu zifuatazo: <br>
        <span class="dotted-line" style="width: 100%;">{{ $leaveRequest->admin_remarks ?? '............................................................................................................' }}</span>
    </div>

    <div class="mt-3">
        Sahihi
        @if($leaveRequest->admin_signature)
            <img src="{{ public_path($leaveRequest->admin_signature) }}" class="signature-img">
        @else
            <span class="dotted-line">........................</span>
        @endif
        Cheo <span class="dotted-line">Afisa Mwajiri</span>
        Tarehe <span class="dotted-line">{{ $leaveRequest->admin_signed_at ?? '........................' }}</span>
    </div>

</body>
</html>
