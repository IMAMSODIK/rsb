<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><title>{{ $title }}</title><style>body{font-family:Arial,sans-serif;font-size:11px}h2{text-align:center}.meta{margin-bottom:16px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #333;padding:5px;text-align:left}th{text-align:center;vertical-align:middle;white-space:nowrap}.no-print{margin-bottom:12px}@media print{.no-print{display:none}}</style></head>
<body onload="window.print()">
    <div class="no-print"><button onclick="window.print()">Cetak / Simpan sebagai PDF</button></div>
    <h2>{{ $title }}</h2>
    <div class="meta"><p>Unit kerja: {{ $report['unit_kerja'] }}</p><p>Periode: {{ $report['periode'] }}</p><p>Mode: {{ $report['mode'] }}</p></div>
    @php $years = !empty($report['rows']) ? array_keys($report['rows'][0]['detail']) : []; @endphp
    <table><thead><tr><th rowspan="2">No</th><th rowspan="2">Kode</th><th rowspan="2">Uraian</th>@foreach($years as $year)<th colspan="{{ 1 + ($report['mode'] !== 'semester_2' ? 3 : 0) + ($report['mode'] !== 'semester_1' ? 3 : 0) + 1 }}">{{ $year }}</th>@endforeach</tr><tr>@foreach($years as $year)<th>Target</th>@if($report['mode'] !== 'semester_2')<th>Realisasi Semester 1</th><th>Keterangan Semester 1</th><th>Dokumen Semester 1</th>@endif @if($report['mode'] !== 'semester_1')<th>Realisasi Semester 2</th><th>Keterangan Semester 2</th><th>Dokumen Semester 2</th>@endif<th>Pencapaian</th>@endforeach</tr></thead><tbody>
        @foreach($report['rows'] as $index => $row)<tr><td>{{ $index + 1 }}</td><td>{{ $row['kode'] }}</td><td>{{ $row['uraian'] }}</td>@foreach($years as $year)@php $detail=$row['detail'][$year] ?? []; @endphp<td>{{ $detail['target'] ?? '-' }}</td>@if($report['mode'] !== 'semester_2')<td>{{ $detail['semester_1']['nilai'] ?? '-' }}</td><td>{{ $detail['semester_1']['keterangan'] ?? '-' }}</td><td>{{ collect($detail['dokumen'][$year.'_1'] ?? [])->pluck('nama_asli')->implode(', ') ?: '-' }}</td>@endif @if($report['mode'] !== 'semester_1')<td>{{ $detail['semester_2']['nilai'] ?? '-' }}</td><td>{{ $detail['semester_2']['keterangan'] ?? '-' }}</td><td>{{ collect($detail['dokumen'][$year.'_2'] ?? [])->pluck('nama_asli')->implode(', ') ?: '-' }}</td>@endif<td>{{ $detail['persentase'] === null ? '-' : $detail['persentase'].'%' }}</td>@endforeach</tr>@endforeach
    </tbody></table>
</body></html>
