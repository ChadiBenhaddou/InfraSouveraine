@props(['label', 'value', 'class' => 'text-gray-900'])
<div class="flex items-center justify-between">
    <dt class="text-gray-500">{{ $label }}</dt>
    <dd class="font-medium {{ $class }}">{{ $value }}</dd>
</div>
