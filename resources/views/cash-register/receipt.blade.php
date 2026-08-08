@extends('layouts.admin')
@section('page-title', 'Чек №'.$transaction->id)

@push('styles')
<style>
    .receipt-page{min-height:calc(100vh - 80px);background:#f4f7f5;padding:28px}.receipt-wrap{width:360px;max-width:100%;margin:auto}.receipt-actions{display:flex;gap:9px;margin-bottom:14px}.receipt-actions a,.receipt-actions button{flex:1;border:0;border-radius:10px;background:#fff;padding:11px;color:#253029;text-align:center;text-decoration:none;font-size:12px;font-weight:800;cursor:pointer}.receipt-actions button{background:#07933d;color:#fff}.receipt{background:#fff;padding:26px 22px;box-shadow:0 18px 50px rgba(30,50,36,.1);font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:#202620}.receipt-logo{text-align:center}.receipt-logo strong{display:block;font-family:Arial,sans-serif;font-size:24px}.receipt-logo span{font-size:10px;color:#7c847e}.receipt-dash{margin:17px 0;border-top:1px dashed #aeb5af}.receipt-row{display:flex;justify-content:space-between;gap:12px;margin:7px 0;font-size:11px}.receipt-item{margin:12px 0}.receipt-item-name{font-size:11px;font-weight:800}.receipt-item-detail{display:flex;justify-content:space-between;margin-top:4px;color:#656d67;font-size:10px}.receipt-total{display:flex;justify-content:space-between;font-size:16px;font-weight:900}.receipt-thanks{text-align:center;margin-top:18px;font-size:10px;line-height:1.6}@media print{body *{visibility:hidden}.receipt,.receipt *{visibility:visible}.receipt{position:absolute;left:0;top:0;width:80mm;box-shadow:none;padding:7mm}.receipt-page{padding:0;background:#fff}.receipt-actions,.sidebar,header,nav{display:none!important}}
</style>
@endpush

@section('content')
<div class="receipt-page"><div class="receipt-wrap">
    @if(session('status'))<div class="mb-3 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>@endif
    <div class="receipt-actions"><a href="{{ route('cash-register.index') }}">В кассу</a><button type="button" onclick="window.print()">Распечатать чек</button></div>
    <article class="receipt">
        <div class="receipt-logo"><strong>PANZE</strong><span>Кассовый чек</span></div><div class="receipt-dash"></div>
        <div class="receipt-row"><span>Чек</span><strong>№{{ $transaction->id }}</strong></div><div class="receipt-row"><span>Смена</span><strong>№{{ $transaction->cash_shift_id }}</strong></div><div class="receipt-row"><span>Дата</span><strong>{{ $transaction->created_at->format('d.m.Y H:i') }}</strong></div><div class="receipt-dash"></div>
        @forelse(data_get($transaction->receipt_data, 'items', []) as $item)<div class="receipt-item"><div class="receipt-item-name">{{ $item['name'] }}</div><div class="receipt-item-detail"><span>{{ $item['quantity'] }} × {{ number_format($item['price'],2,',',' ') }}</span><strong>{{ number_format($item['total'],2,',',' ') }} KZT</strong></div></div>@empty<div class="receipt-row"><span>{{ $transaction->description }}</span><strong>{{ number_format((float)$transaction->amount,2,',',' ') }} KZT</strong></div>@endforelse
        <div class="receipt-dash"></div><div class="receipt-total"><span>ИТОГО</span><span>{{ number_format((float)$transaction->amount,2,',',' ') }} KZT</span></div><div class="receipt-thanks">Спасибо за покупку!<br>Сохраняйте чек до конца обслуживания.</div>
    </article>
</div></div>
@endsection
