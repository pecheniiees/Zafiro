@extends('settings.layout')

@section('settings-section', 'tariffs')

@section('settings-content')
@php($zoneTariffs = $settings['tariffs']['zones'] ?? [])

@if ($zones->isEmpty())
    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-5 py-10 text-center dark:border-gray-700 dark:bg-gray-950/40">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-white text-gray-500 shadow-sm dark:bg-gray-900 dark:text-gray-400">
            <i class="fas fa-layer-group"></i>
        </div>
        <p class="mt-4 font-semibold text-gray-900 dark:text-white">Нет зон с ПК</p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Добавьте ПК в нужные зоны на карте зала, и здесь появятся вкладки тарифов только для этих зон.</p>
        <a href="{{ route('club-map.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">
            <i class="fas fa-map-location-dot"></i>
            <span>Открыть карту зала</span>
        </a>
    </div>
@else
    <div class="space-y-5" data-zone-tariffs>
        <div class="overflow-x-auto border-b border-gray-200 dark:border-gray-800">
            <div class="flex min-w-max gap-2" role="tablist" aria-label="Зоны тарифов">
                @foreach ($zones as $zone)
                    <button type="button" class="zone-tariff-tab rounded-t-lg px-4 py-3 text-sm font-semibold transition {{ $loop->first ? 'bg-purple-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}" data-zone-tab="{{ $zone->id }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        <i class="fas fa-{{ $zone->icon }} mr-2"></i>{{ $zone->name }}
                    </button>
                @endforeach
            </div>
        </div>

        @foreach ($zones as $zone)
            @php($currentTariffs = $zoneTariffs[(string) $zone->id] ?? [])
            <section class="zone-tariff-panel {{ $loop->first ? '' : 'hidden' }}" data-zone-panel="{{ $zone->id }}" role="tabpanel">
                <div class="mb-5 flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $zone->name }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $zone->description ?: 'Ручные тарифы для этой зоны клуба.' }}</p>
                    </div>
                    <button type="button" data-open-tariff-modal data-zone-id="{{ $zone->id }}" data-zone-name="{{ $zone->name }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">
                        <i class="fas fa-plus"></i>
                        <span>Добавить тариф</span>
                    </button>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-950/40 dark:text-gray-400">
                            <tr><th class="px-4 py-3">Название</th><th class="px-4 py-3 text-right">Цена</th><th class="px-4 py-3 text-right">Длительность</th><th class="px-4 py-3 text-right">Удалить</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800" data-tariff-list="{{ $zone->id }}">
                            @forelse ($currentTariffs as $index => $tariff)
                                <tr data-tariff-row>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $tariff['name'] ?? 'Тариф' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ number_format((float) ($tariff['price'] ?? 0), 0, ',', ' ') }} KZT</td>
                                    <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ $tariff['duration_minutes'] ?? 60 }} мин</td>
                                    <td class="px-4 py-3 text-right"><button type="button" data-remove-tariff class="text-red-600 hover:text-red-700"><i class="fas fa-trash"></i></button></td>
                                    <td class="hidden"><input name="zones[{{ $zone->id }}][tariffs][{{ $index }}][name]" value="{{ $tariff['name'] ?? '' }}"><input name="zones[{{ $zone->id }}][tariffs][{{ $index }}][price]" value="{{ $tariff['price'] ?? 0 }}"><input name="zones[{{ $zone->id }}][tariffs][{{ $index }}][duration_minutes]" value="{{ $tariff['duration_minutes'] ?? 60 }}"></td>
                                </tr>
                            @empty
                                <tr data-empty-tariffs><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Тарифов пока нет. Добавьте тариф вручную.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>

    <div id="tariff-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-950/85 p-4 backdrop-blur-md" aria-hidden="true">
        <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div><p class="text-xs font-semibold uppercase tracking-wide text-purple-600 dark:text-purple-400" id="tariff-zone-name">Зона</p><h3 class="mt-1 text-lg font-bold text-gray-900 dark:text-white">Добавить тариф</h3></div>
                <button type="button" data-close-tariff-modal class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-4 p-5">
                <input type="hidden" id="tariff-zone-id">
                <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Название тарифа</label><input id="tariff-name" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" placeholder="Например: 1 час"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Цена</label><input id="tariff-price" type="number" min="0" step="50" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" value="0"></div>
                    <div><label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Минут</label><input id="tariff-duration" type="number" min="1" step="5" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" value="60"></div>
                </div>
            </div>
            <div class="flex justify-end gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-800"><button type="button" data-close-tariff-modal class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">Отмена</button><button type="button" id="add-tariff" class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-purple-700">Добавить</button></div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const root = document.querySelector('[data-zone-tariffs]');
    if (! root) return;
    const tabs = Array.from(root.querySelectorAll('[data-zone-tab]'));
    const panels = Array.from(root.querySelectorAll('[data-zone-panel]'));
    tabs.forEach(tab => tab.addEventListener('click', () => { tabs.forEach(item => { const active = item === tab; item.setAttribute('aria-selected', active ? 'true' : 'false'); item.classList.toggle('bg-purple-600', active); item.classList.toggle('text-white', active); item.classList.toggle('text-gray-600', !active); item.classList.toggle('dark:text-gray-300', !active); item.classList.toggle('hover:bg-gray-100', !active); item.classList.toggle('dark:hover:bg-gray-800', !active); }); panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.zonePanel !== tab.dataset.zoneTab)); }));

    const modal = document.getElementById('tariff-modal'), zoneId = document.getElementById('tariff-zone-id'), zoneName = document.getElementById('tariff-zone-name'), nameInput = document.getElementById('tariff-name'), priceInput = document.getElementById('tariff-price'), durationInput = document.getElementById('tariff-duration');
    const open = button => { zoneId.value = button.dataset.zoneId; zoneName.textContent = button.dataset.zoneName; nameInput.value = ''; priceInput.value = '0'; durationInput.value = '60'; modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); document.body.classList.add('overflow-hidden'); nameInput.focus(); };
    const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); document.body.classList.remove('overflow-hidden'); };
    root.querySelectorAll('[data-open-tariff-modal]').forEach(button => button.addEventListener('click', () => open(button)));
    modal.querySelectorAll('[data-close-tariff-modal]').forEach(button => button.addEventListener('click', close));
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('click', event => { const button = event.target.closest('[data-remove-tariff]'); if (button) { const row = button.closest('[data-tariff-row]'); const list = row.parentElement; row.remove(); if (!list.querySelector('[data-tariff-row]')) list.insertAdjacentHTML('beforeend', '<tr data-empty-tariffs><td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Тарифов пока нет. Добавьте тариф вручную.</td></tr>'); } });
    document.getElementById('add-tariff').addEventListener('click', () => { const list = root.querySelector(`[data-tariff-list="${zoneId.value}"]`); const index = list.querySelectorAll('[data-tariff-row]').length; const name = nameInput.value.trim(); if (!name) { nameInput.focus(); return; } const price = Math.max(0, Number(priceInput.value || 0)); const duration = Math.max(1, Number(durationInput.value || 60)); list.querySelector('[data-empty-tariffs]')?.remove(); list.insertAdjacentHTML('beforeend', `<tr data-tariff-row><td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100"></td><td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">${price.toLocaleString('ru-RU')} KZT</td><td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">${duration} мин</td><td class="px-4 py-3 text-right"><button type="button" data-remove-tariff class="text-red-600 hover:text-red-700"><i class="fas fa-trash"></i></button></td><td class="hidden"><input name="zones[${zoneId.value}][tariffs][${index}][name]"><input name="zones[${zoneId.value}][tariffs][${index}][price]" value="${price}"><input name="zones[${zoneId.value}][tariffs][${index}][duration_minutes]" value="${duration}"></td></tr>`); const row = list.lastElementChild; row.children[0].textContent = name; row.querySelector('input').value = name; close(); });
})();
</script>
@endpush
