@if (count($breadcrumbs))
<nav class="max-w-7xl mx-auto px-4 pt-4 w-full text-sm text-gray-500 dark:text-gray-400" aria-label="Хлебные крошки">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($loop->last)
                <li class="text-gray-800 dark:text-gray-100 font-medium" aria-current="page">{{ $breadcrumb->title }}</li>
            @else
                <li>
                    <a href="{{ $breadcrumb->url }}" class="hover:text-indigo-600">{{ $breadcrumb->title }}</a>
                </li>
                <li class="text-gray-300 dark:text-gray-600" aria-hidden="true">/</li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
