<nav class="max-w-7xl mx-auto px-4 pt-4 w-full text-sm text-gray-500" aria-label="Хлебные крошки">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($breadcrumbs as $crumb)
            @if ($loop->last)
                <li class="text-gray-800 font-medium" aria-current="page">{{ $crumb['label'] }}</li>
            @else
                <li>
                    <a href="{{ $crumb['url'] }}" class="hover:text-indigo-600">{{ $crumb['label'] }}</a>
                </li>
                <li class="text-gray-300" aria-hidden="true">/</li>
            @endif
        @endforeach
    </ol>
</nav>
