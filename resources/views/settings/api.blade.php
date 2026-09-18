@extends('settings.layout')

@section('settings-section', 'api')

@section('settings-content')
<h4 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">API</h4>
<p class="mb-2 text-sm text-gray-600 dark:text-gray-400">Все зарегистрированные API endpoint-ы проекта и отдельные GET API для настроек.</p>
<p class="mb-4 text-sm font-medium text-gray-700 dark:text-gray-300">IP: {{ $ip }}</p>

<div class="mb-6 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800">
        <h5 class="font-semibold text-gray-900 dark:text-white">Все API routes</h5>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
            <thead class="bg-white dark:bg-gray-900">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Метод</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">URI</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">URL</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Route</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Middleware</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                @foreach ($apiRoutes as $route)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-purple-700 dark:text-purple-300">{{ implode('|', $route['methods']) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ $route['uri'] }}</td>
                        <td class="min-w-80 px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300"><span class="break-all">{{ $route['url'] }}</span></td>
                        <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $route['name'] ?: '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">{{ implode(', ', $route['middleware']) ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="flex flex-col gap-4">
    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
        <h5 class="mb-2 font-semibold text-gray-900 dark:text-white">
            {{ $shellResolveEndpoint['name'] }}
        </h5>
        <p class="mb-2 font-mono text-xs text-gray-500 dark:text-gray-400">shell.resolve</p>
        <p class="mb-3 break-all rounded-lg bg-white px-3 py-2 font-mono text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            POST {{ $shellResolveEndpoint['url'] }}
        </p>
        <div class="mb-3 rounded-lg border border-amber-200 bg-white px-3 py-2 dark:border-amber-800 dark:bg-gray-800">
            <p class="mb-1 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Ключ этого клуба</p>
            <p class="break-all font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $shellKey }}</p>
        </div>
        <div class="grid gap-3 lg:grid-cols-2">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Request</p>
                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode($shellResolveEndpoint['request'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
            <div>
                <p class="mb-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Response</p>
                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode($shellResolveEndpoint['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    </div>
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
        <h5 class="mb-2 font-semibold text-gray-900 dark:text-white">
            {{ $authEndpoint['name'] }}
        </h5>
        <p class="mb-2 font-mono text-xs text-gray-500 dark:text-gray-400">auth.login</p>
        <p class="mb-3 break-all rounded-lg bg-white px-3 py-2 font-mono text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            POST {{ $authEndpoint['url'] }}
        </p>
        <div class="grid gap-3 lg:grid-cols-2">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Request</p>
                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode($authEndpoint['request'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
            <div>
                <p class="mb-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Response</p>
                <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode($authEndpoint['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
            </div>
        </div>
    </div>

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

    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
        <h5 class="mb-2 font-semibold text-gray-900 dark:text-white">
            {{ $tariffEndpoint['name'] }}
        </h5>
        <p class="mb-2 font-mono text-xs text-gray-500 dark:text-gray-400">tariffs.index</p>
        <p class="mb-3 break-all rounded-lg bg-white px-3 py-2 font-mono text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-300">
            GET {{ $tariffEndpoint['url'] }}
        </p>
        <pre class="overflow-x-auto rounded-lg bg-gray-950 p-4 text-sm text-gray-200"><code>{{ json_encode($tariffEndpoint['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code></pre>
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
