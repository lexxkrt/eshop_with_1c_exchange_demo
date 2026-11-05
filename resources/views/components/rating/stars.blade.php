@props(['avg' => 0])

@for ($i = 1; $i <= 5; $i++)
    {{ $i <= round($avg) ? '★' : '☆' }}
@endfor
