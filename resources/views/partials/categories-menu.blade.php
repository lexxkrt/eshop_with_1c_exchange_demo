{{-- Список категорий для меню в шапке: используется и в десктопном
    выпадающем меню, и в мобильном <details> --}}
<a href="{{ route('catalog.index') }}"
   class="block px-4 py-2 font-semibold hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-gray-700 dark:hover:text-indigo-400">
    Все товары
</a>
@foreach ($menuCategories as $category)
    <div class="px-4 py-2 hover:bg-indigo-50 dark:hover:bg-gray-700">
        <a href="{{ route('categories.show', $category->slug) }}"
           class="block font-semibold hover:text-indigo-600 dark:hover:text-indigo-400">
            {{ $category->name }}
        </a>
        @if ($category->children->isNotEmpty())
            <div class="mt-1 space-y-1">
                @foreach ($category->children as $child)
                    <a href="{{ route('categories.show', $child->slug) }}"
                       class="block pl-4 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">
                        — {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endforeach
