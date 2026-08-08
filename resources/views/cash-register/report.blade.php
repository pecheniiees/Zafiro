@extends('layouts.admin')
@section('page-title', $reportType.'-отчёт')

@push('styles')
<style>
    .report-page{min-height:calc(100vh - 80px);background:#f4f7f5;padding:28px}.report-wrap{max-width:760px;margin:auto}.report-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}.report-back{color:#4d5850;text-decoration:none;font-size:13px}.report-actions{display:flex;gap:9px}.report-btn{border:0;border-radius:10px;padding:11px 16px;background:#fff;color:#263029;font-size:12px;font-weight:800;cursor:pointer}.report-btn.primary{background:#07933d;color:#fff}.report-btn.danger{background:#1c2520;color:#fff}.report-btn:disabled{opacity:.4;cursor:not-allowed}.report-paper{position:relative;overflow:hidden;border:1px solid #e3e9e4;border-radius:20px;background:#fff;box-shadow:0 18px 55px rgba(32,55,40,.08)}.report-head{display:flex;justify-content:space-between;gap:18px;padding:28px;background:linear-gradient(135deg,#103c25,#07933d);color:#fff}.report-type{display:inline-flex;border-radius:20px;background:rgba(255,255,255,.16);padding:6px 10px;font-size:10px;font-weight:800;letter-spacing:.12em}.report-head h1{margin:10px 0 4px;font-size:28px}.report-head p{margin:0;color:#d7f0df;font-size:12px}.report-state{align-self:flex-start;border-radius:10px;background:#fff;color:#176434;padding:9px 12px;font-size:11px;font-weight:800}.report-body{padding:26px}.report-info{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:22px}.report-info div{border:1px solid #edf1ed;border-radius:12px;padding:13px}.report-info span{display:block;margin-bottom:5px;color:#89918b;font-size:10px}.report-info strong{font-size:12px}.report-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.report-metric{border-radius:14px;background:#f6f8f6;padding:17px}.report-metric span{display:block;color:#7d877f;font-size:11px}.report-metric strong{display:block;margin-top:7px;font-size:18px}.report-metric.income strong{color:#07933d}.report-metric.expense strong{color:#d24b3e}.report-total{display:flex;align-items:center;justify-content:space-between;margin-top:16px;border-radius:15px;background:#d9f2e1;padding:20px}.report-total span{font-size:13px;font-weight:700}.report-total strong{font-size:24px;color:#086b30}.report-note{margin-top:18px;border-left:3px solid #e7b735;background:#fffaf0;padding:12px 14px;color:#74602e;font-size:11px;line-height:1.5}.report-error{margin-bottom:14px;border-radius:10px;background:#fff0ee;padding:12px;color:#a72d23;font-size:12px}@media(max-width:650px){.report-page{padding:12px}.report-toolbar{align-items:flex-start;flex-direction:column}.report-info,.report-metrics{grid-template-columns:1fr}.report-head{padding:20px}.report-body{padding:18px}}@media print{body *{visibility:hidden}.report-paper,.report-paper *{visibility:visible}.report-paper{position:absolute;left:0;top:0;width:100%;border:0;border-radius:0;box-shadow:none}.report-page{padding:0;background:#fff}.report-toolbar,.report-error,.sidebar,header,nav{display:none!important}.report-head{-webkit-print-color-adjust:exact;print-color-adjust:exact}.report-total{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
@endpush

@section('content')
<div class="report-page"><div class="report-wrap">
    @if($errors->any())<div class="report-error">{{ $errors->first() }}</div>@endif
    <div class="report-toolbar">
        <a class="report-back" href="{{ route('cash-register.index') }}">← Вернуться в кассу</a>
        <div class="report-actions">
            <button type="button" id="print-report" class="report-btn primary">Распечатать {{ $reportType }}-отчёт</button>
            @if($reportType === 'Z' && $shift->status === \App\Models\CashShift::STATUS_OPEN)
                <form method="POST" action="{{ route('cash-register.shifts.close', $shift) }}">@csrf<button id="close-shift" class="report-btn danger" disabled>Закрыть смену</button></form>
            @endif
        </div>
    </div>
    <article class="report-paper">
        <header class="report-head"><div><span class="report-type">{{ $reportType }} REPORT</span><h1>{{ $reportType }}-отчёт</h1><p>{{ $reportType === 'X' ? 'Промежуточный отчёт без закрытия смены' : 'Итоговый отчёт кассовой смены' }}</p></div><span class="report-state">{{ $shift->status === 'open' ? 'Смена открыта' : 'Смена закрыта' }}</span></header>
        <div class="report-body">
            <div class="report-info"><div><span>Номер смены</span><strong>#{{ $shift->id }}</strong></div><div><span>Открыта</span><strong>{{ $shift->opened_at->format('d.m.Y H:i') }}</strong></div><div><span>{{ $shift->closed_at ? 'Закрыта' : 'Сформирован' }}</span><strong>{{ ($shift->closed_at ?? now())->format('d.m.Y H:i') }}</strong></div></div>
            <div class="report-metrics"><div class="report-metric"><span>Начальный остаток</span><strong>{{ number_format((float)$shift->opening_balance,2,',',' ') }} KZT</strong></div><div class="report-metric income"><span>Приход</span><strong>+ {{ number_format($totals['income'],2,',',' ') }} KZT</strong></div><div class="report-metric expense"><span>Расход</span><strong>− {{ number_format($totals['expense'],2,',',' ') }} KZT</strong></div></div>
            <div class="report-total"><span>Итог в кассе</span><strong>{{ number_format($totals['balance'],2,',',' ') }} KZT</strong></div>
            @if($reportType === 'Z' && $shift->status === 'open')<div class="report-note">Смена останется открытой, пока Z-отчёт не будет отправлен на печать. После завершения диалога печати кнопка закрытия станет активной.</div>@endif
        </div>
    </article>
</div></div>
@endsection

@push('scripts')
<script>
(() => {
    const printButton = document.getElementById('print-report');
    const closeButton = document.getElementById('close-shift');
    let printRequested = false;
    printButton?.addEventListener('click', () => { printRequested = true; window.print(); });
    @if($reportType === 'Z' && $shift->status === \App\Models\CashShift::STATUS_OPEN)
    window.addEventListener('afterprint', async () => {
        if (!printRequested) return;
        printRequested = false;
        const response = await fetch(@json(route('cash-register.z-report.printed', $shift)), {method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token()),'Accept':'application/json'},body:JSON.stringify({nonce:@json($printNonce)})});
        if (response.ok && closeButton) { closeButton.disabled = false; printButton.textContent = 'Z-отчёт распечатан'; }
    });
    @endif
})();
</script>
@endpush
