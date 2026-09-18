@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-8">
    <h1 class="text-2xl font-bold mb-6">ダッシュボード</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">閲覧数</p>
            <h3 class="text-3xl font-extrabold text-gray-900">{{ number_format($totalViews) }}</h3>
            <span class="text-xs font-semibold text-gray-400">---</span>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">いいね数</p>
            <h3 class="text-3xl font-extrabold text-gray-900">{{ number_format($totalLikes) }}</h3>
            <span class="text-xs font-semibold text-gray-400">---</span>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-xs font-semibold text-gray-400 uppercase mb-1">購入数</p>
            <div class="flex items-baseline justify-between">
                <h3 class="text-3xl font-extrabold text-gray-900">{{ number_format($totalSalesCount) }}</h3>
                @if(isset($salesDiff))
                    @if($salesDiff >= 0)
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">先週比 +{{ $salesDiff }}</span>
                    @else
                        <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded">先週比 {{ $salesDiff }}</span>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="font-bold mb-4">売れ筋商品ランキング</h2>
            @if(isset($productRanking) && $productRanking->count() > 0)
                <ul class="divide-y divide-gray-100">
                    @foreach($productRanking as $index => $product)
                        <li class="py-3 flex justify-between items-center">
                            <div class="flex items-center space-x-3 truncate mr-2">
                                <span class="font-bold text-sm text-gray-400">{{ $index + 1 }}位</span>
                                <span class="text-sm font-medium text-gray-800 truncate">{{ $product->name }}</span>
                            </div>
                            <div class="flex items-center space-x-2 shrink-0">
                                <span class="text-xs font-semibold bg-rose-50 text-rose-600 px-2 py-1 rounded">
                                    いいね {{ $product->likes_count ?? 0 }}
                                </span>
                                <span class="text-xs font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded">
                                    購入 {{ $product->total_sold ?? 0 }}件
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-sm text-gray-500">販売データがありません。</p>
            @endif
        </div>

        <div class="md:col-span-2 bg-white p-6 rounded-lg shadow">
            <h2 class="font-bold mb-4">年代別 売上推移 月ごと</h2>
            <canvas id="salesChart" class="max-h-[350px]"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white p-6 rounded-lg shadow md:col-span-1">
            <h2 class="font-bold mb-4">購入者の性別比率</h2>
            <canvas id="genderChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: @json($datasets)
        },
        options: {
            responsive: true,
            scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } },
            plugins: { legend: { position: 'bottom' } }
        }
    });

    const genderLabels = {!! json_encode($genderStats->pluck('gender')) !!};
    const genderData = {!! json_encode($genderStats->pluck('total_sales')) !!};

    const genderColorMap = {
        '女性': '#f472b6',
        '男性': '#3b82f6',
        'その他': '#34d399',
        '不明': '#9ca3af'
    };

    const genderBackgroundColors = genderLabels.map(label => {
        return genderColorMap[label] || '#9ca3af';
    });

    new Chart(document.getElementById('genderChart'), {
        type: 'pie',
        data: {
            labels: genderLabels,
            datasets: [{
                data: genderData,
                backgroundColor: genderBackgroundColors
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endsection