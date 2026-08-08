@extends('layouts.admin')
@section('page-title', 'Касса POS')

@push('styles')
<style>
    .pos-page{background:#f4f6f5;border-radius:24px;padding:18px;color:#17201b}.pos-top{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:14px}.pos-status{font-size:13px;color:#788078}.pos-actions{display:flex;gap:8px}.pos-actions a,.pos-actions button{border:0;border-radius:10px;padding:9px 14px;background:#fff;font-size:12px;font-weight:700}.pos-actions .danger{color:#b42318}.pos-layout{display:grid;grid-template-columns:minmax(0,1fr) 370px;gap:16px}.pos-main,.pos-order{background:#fff;border:1px solid #edf0ed;border-radius:18px;padding:14px}.pos-categories{display:grid;grid-template-columns:repeat(6,minmax(88px,1fr));gap:10px;margin-bottom:16px}.pos-category{border:1px solid #edf0ed;border-radius:13px;background:#fff;padding:10px;text-align:left;color:#5d665f;cursor:pointer;transition:.2s}.pos-category.active{background:#ccebd7;border-color:#ccebd7;color:#17201b}.pos-category-icon{display:block;font-size:22px;line-height:1;margin-bottom:14px;color:#149447}.pos-category strong{display:block;font-size:12px}.pos-category small{display:block;margin-top:3px;font-size:9px;color:#8b938d}.pos-products{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.pos-product{position:relative;border:1px solid #edf0ed;border-radius:15px;padding:7px;background:#fff;transition:.2s}.pos-product.selected{border-color:#47ba70;box-shadow:0 0 0 1px #47ba70}.pos-product img{width:100%;height:135px;border-radius:11px;background:#f4f5f4;object-fit:contain}.pos-product h4{min-height:36px;margin:9px 3px 2px;font-size:12px;line-height:1.35}.pos-product-meta{margin:0 3px 8px;font-size:9px;color:#8b938d}.pos-product-price{display:flex;align-items:center;justify-content:space-between;margin:0 3px 8px;font-size:12px;font-weight:800}.pos-stock{color:#159447;font-size:9px;font-weight:600}.pos-stock.low{color:#d65c4b}.pos-add{width:100%;border:0;border-radius:8px;background:#d5f0dd;padding:8px;color:#16733a;font-size:10px;font-weight:700;cursor:pointer}.pos-add:disabled{opacity:.5;cursor:not-allowed}.pos-tables{display:flex;gap:10px;overflow:auto;margin-top:14px}.pos-table{display:flex;align-items:center;gap:8px;min-width:145px;border:1px solid #edf0ed;border-radius:24px;background:#fff;padding:7px 10px}.pos-table span{display:grid;place-items:center;width:28px;height:28px;border-radius:50%;background:#ffcf4b;font-size:10px}.pos-table strong{display:block;font-size:10px}.pos-table small{display:block;color:#929a94;font-size:8px}.pos-order-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}.pos-order-head h3{font-size:16px;font-weight:800}.pos-modes{display:flex;background:#f1f3f1;border-radius:9px;padding:3px}.pos-mode{border:0;border-radius:7px;background:transparent;padding:7px 10px;font-size:9px;cursor:pointer}.pos-mode.active{background:#fff;box-shadow:0 2px 8px #dfe4df}.pos-cart{min-height:320px;max-height:420px;overflow:auto}.pos-empty{padding:80px 20px;text-align:center;color:#9ba29d;font-size:13px}.pos-cart-item{display:grid;grid-template-columns:52px minmax(0,1fr);gap:9px;padding:9px 0;border-bottom:1px solid #f0f2f0}.pos-cart-item img{width:52px;height:52px;border-radius:10px;background:#f5f6f5}.pos-cart-name{font-size:11px;font-weight:700}.pos-cart-sub{font-size:9px;color:#919991;margin-top:3px}.pos-cart-line{display:flex;align-items:center;justify-content:space-between;margin-top:7px}.pos-cart-price{font-size:11px;font-weight:800;color:#159447}.pos-qty{display:flex;align-items:center;gap:7px}.pos-qty button{display:grid;place-items:center;width:23px;height:23px;border:0;border-radius:50%;background:#eef1ee;font-weight:800;cursor:pointer}.pos-summary{margin-top:12px;border:1px solid #edf0ed;border-radius:12px;padding:12px}.pos-summary-row{display:flex;justify-content:space-between;margin-bottom:7px;font-size:11px;color:#778078}.pos-summary-total{display:flex;justify-content:space-between;border-top:1px dashed #dfe4df;padding-top:10px;font-size:14px;font-weight:800}.pos-payments{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:12px 0}.pos-payment{border:1px solid #e6eae6;border-radius:11px;background:#fff;padding:11px 5px;font-size:9px;cursor:pointer}.pos-payment span{display:block;font-size:17px;margin-bottom:5px}.pos-payment.active{background:#ccebd7;border-color:#ccebd7}.pos-checkout{width:100%;border:0;border-radius:10px;background:#07933d;padding:13px;color:#fff;font-size:12px;font-weight:800;cursor:pointer}.pos-checkout:disabled{opacity:.45}.pos-open{max-width:560px;margin:30px auto;border-radius:18px;background:#fff;padding:26px}.pos-open h3{margin-bottom:18px;font-size:20px;font-weight:800}.pos-open form{display:flex;align-items:end;gap:12px}.pos-open label{display:block;margin-bottom:6px;font-size:12px}.pos-open input{border:1px solid #dfe4df;border-radius:10px;padding:10px 12px}.pos-open button{border:0;border-radius:10px;background:#07933d;padding:11px 18px;color:#fff;font-weight:700}@media(max-width:1100px){.pos-layout{grid-template-columns:1fr}.pos-categories{grid-template-columns:repeat(3,1fr)}}@media(max-width:700px){.pos-page{padding:10px}.pos-products{grid-template-columns:repeat(2,minmax(0,1fr))}.pos-categories{display:flex;overflow:auto}.pos-category{min-width:100px}.pos-product img{height:110px}.pos-top{align-items:flex-start;flex-direction:column}.pos-order{padding:11px}}
</style>
@endpush

@section('content')
<div class="p-4 lg:p-6">
    @if(session('status'))<div class="mb-4 rounded-lg bg-green-50 p-4 text-green-700">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="mb-4 rounded-lg bg-red-50 p-4 text-red-700">{{ $errors->first() }}</div>@endif

    @if(!$shift)
        <div class="pos-open">
            <h3>Открыть смену</h3>
            <form method="POST" action="{{ route('cash-register.shifts.open') }}">
                @csrf
                <div><label>Начальный остаток</label><input type="number" step="0.01" min="0" name="opening_balance" required></div>
                <button>Открыть кассу</button>
            </form>
        </div>
    @else
        @php($categories = $products->pluck('category')->filter()->unique()->values())
        <div class="pos-page">
            <div class="pos-top">
                <div class="pos-status">Смена #{{ $shift->id }} · В кассе: <strong>{{ number_format($totals['balance'], 2, ',', ' ') }} KZT</strong></div>
                <div class="pos-actions"><a href="{{ route('cash-register.x-report') }}">X-отчёт</a><a class="danger" href="{{ route('cash-register.z-report', $shift) }}">Закрыть смену · Z-отчёт</a></div>
            </div>
            <div class="pos-layout">
                <main class="pos-main">
                    <div class="pos-categories">
                        <button class="pos-category active" data-category="all"><span class="pos-category-icon">⌘</span><strong>Все</strong><small>{{ $products->count() }} товаров</small></button>
                        @foreach($categories->take(5) as $category)
                            <button class="pos-category" data-category="{{ $category }}"><span class="pos-category-icon">♨</span><strong>{{ $category }}</strong><small>{{ $products->where('category', $category)->count() }} товаров</small></button>
                        @endforeach
                    </div>
                    <div class="pos-products">
                        @foreach($products as $product)
                            <article class="pos-product" data-product-id="{{ $product->id }}" data-category="{{ $product->category }}">
                                <img src="{{ asset('images/product-placeholder.svg') }}" alt="{{ $product->name }}">
                                <h4>{{ $product->name }}</h4>
                                <p class="pos-product-meta">{{ $product->category ?: 'Без категории' }}</p>
                                <div class="pos-product-price"><span>{{ number_format((float)$product->price, 0, ',', ' ') }} ₸</span><span class="pos-stock {{ $product->quantity < 5 ? 'low' : '' }}">{{ $product->quantity > 0 ? 'В наличии: '.$product->quantity.' шт.' : 'Нет в наличии · 0 шт.' }}</span></div>
                                <button type="button" class="pos-add add-product" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ (float)$product->price }}" data-stock="{{ $product->quantity }}" {{ $product->quantity < 1 ? 'disabled' : '' }}>Добавить в заказ</button>
                            </article>
                        @endforeach
                    </div>
                    <div class="pos-tables">
                        @foreach([['T1','Стол 1'],['T2','Стол 2'],['T3','Стол 3']] as [$code,$table])
                            <button type="button" class="pos-table"><span>{{ $code }}</span><span style="display:block;width:auto;height:auto;background:none;text-align:left"><strong>{{ $table }}</strong><small>Свободен · зал</small></span></button>
                        @endforeach
                    </div>
                </main>
                <aside class="pos-order">
                    <div class="pos-order-head"><h3>Новый заказ</h3><div class="pos-modes"><button type="button" class="pos-mode active">В зале</button><button type="button" class="pos-mode">С собой</button><button type="button" class="pos-mode">Доставка</button></div></div>
                    <div id="cart" class="pos-cart"><div class="pos-empty">Добавьте товары в заказ</div></div>
                    <div class="pos-summary">
                        <div class="pos-summary-row"><span>Подытог</span><span id="subtotal">0 ₸</span></div>
                        <div class="pos-summary-row"><span>НДС 5% (включён)</span><span id="tax">0 ₸</span></div>
                        <div class="pos-summary-total"><span>Итого</span><span id="total">0 ₸</span></div>
                    </div>
                    <div class="pos-payments"><button type="button" class="pos-payment active"><span>▣</span>Наличные</button><button type="button" class="pos-payment"><span>▤</span>Карта</button><button type="button" class="pos-payment"><span>▦</span>QR-код</button></div>
                    <form id="sale-form" method="POST" action="{{ route('cash-register.sales.store') }}">@csrf<div id="sale-items"></div><button id="checkout" disabled class="pos-checkout">Оформить заказ · Оплатить</button></form>
                </aside>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const cart = new Map();
    const box = document.getElementById('cart');
    if (!box) return;
    const placeholder = @json(asset('images/product-placeholder.svg'));
    const money = value => new Intl.NumberFormat('ru-RU').format(value) + ' ₸';
    const escapeHtml = value => String(value).replace(/[&<>'"]/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[char]));

    function render() {
        box.innerHTML = '';
        let total = 0;
        cart.forEach((item, id) => {
            total += item.price * item.qty;
            box.insertAdjacentHTML('beforeend', `<div class="pos-cart-item"><img src="${placeholder}" alt=""><div><div class="pos-cart-name">${escapeHtml(item.name)}</div><div class="pos-cart-sub">Без модификаторов</div><div class="pos-cart-line"><span class="pos-cart-price">${money(item.price * item.qty)}</span><div class="pos-qty"><button type="button" data-change="-1" data-id="${id}">−</button><span>${item.qty}</span><button type="button" data-change="1" data-id="${id}">+</button></div></div></div></div>`);
        });
        if (!cart.size) box.innerHTML = '<div class="pos-empty">Добавьте товары в заказ</div>';
        document.getElementById('subtotal').textContent = money(total);
        document.getElementById('tax').textContent = money(Math.round(total * 5 / 105));
        document.getElementById('total').textContent = money(total);
        document.getElementById('checkout').disabled = !cart.size;
        const inputs = document.getElementById('sale-items'); inputs.innerHTML = '';
        let index = 0;
        cart.forEach((item, id) => { inputs.insertAdjacentHTML('beforeend', `<input type="hidden" name="items[${index}][product_id]" value="${id}"><input type="hidden" name="items[${index}][quantity]" value="${item.qty}">`); index++; });
        document.querySelectorAll('.pos-product').forEach(card => card.classList.toggle('selected', cart.has(Number(card.dataset.productId))));
    }

    box.addEventListener('click', event => { const button = event.target.closest('[data-change]'); if (!button) return; const id = Number(button.dataset.id); const item = cart.get(id); if (!item) return; item.qty += Number(button.dataset.change); if (item.qty < 1) cart.delete(id); else item.qty = Math.min(item.qty, item.stock); render(); });
    document.querySelectorAll('.add-product').forEach(button => button.addEventListener('click', () => { const id = Number(button.dataset.id); const item = cart.get(id) || {name: button.dataset.name, price: Number(button.dataset.price), stock: Number(button.dataset.stock), qty: 0}; item.qty = Math.min(item.qty + 1, item.stock); cart.set(id, item); render(); }));
    document.querySelectorAll('.pos-category').forEach(button => button.addEventListener('click', () => { document.querySelectorAll('.pos-category').forEach(item => item.classList.remove('active')); button.classList.add('active'); document.querySelectorAll('.pos-product').forEach(card => card.style.display = button.dataset.category === 'all' || card.dataset.category === button.dataset.category ? '' : 'none'); }));
    document.querySelectorAll('.pos-mode').forEach(button => button.addEventListener('click', () => { document.querySelectorAll('.pos-mode').forEach(item => item.classList.remove('active')); button.classList.add('active'); }));
    document.querySelectorAll('.pos-payment').forEach(button => button.addEventListener('click', () => { document.querySelectorAll('.pos-payment').forEach(item => item.classList.remove('active')); button.classList.add('active'); }));
})();
</script>
@endpush
