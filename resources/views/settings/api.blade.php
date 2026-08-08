@extends('settings.layout')

@section('settings-section', 'api')

@section('settings-content')
<h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">API</h4>
<p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Каждая настройка доступна через отдельный GET API.</p>
<p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">IP: {{ $ip }}</p>

<div class="flex flex-col gap-4">
    <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-900/20">
        <h5 class="mb-2 font-semibold text-gray-900 dark:text-white">
            {{ $productEndpoint['name'] }}
        </h5>
        <p class="mb-2 font-mono text-xs text-gray-500 dark:text-gray-400">products.index</p>
        <p class="mb-3 break-all rounded-lg bg-white px-3 py-2 font-mono text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            GET {{ $productEndpoint['url'] }}
        </p>
        <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode($productEndpoint['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
    </div>

    @foreach ($endpoints as $endpoint)
        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <h5 class="mb-2 font-semibold text-gray-900 dark:text-white">
                {{ $endpoint['name'] }}
            </h5>
            <p class="mb-2 font-mono text-xs text-gray-500 dark:text-gray-400">
                {{ $endpoint['section'] }}.{{ $endpoint['key'] }}
            </p>
            <p class="mb-3 break-all rounded-lg bg-gray-100 px-3 py-2 font-mono text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                GET {{ $endpoint['url'] }}
            </p>
            <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode([
                'ip' => $ip,
                'setting' => $endpoint['section'].'.'.$endpoint['key'],
                'name' => $endpoint['name'],
                'value' => $endpoint['value'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
        </div>
    @endforeach
</div>
@endsection
