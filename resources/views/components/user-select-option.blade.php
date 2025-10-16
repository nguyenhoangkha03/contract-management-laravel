<div class="flex items-center gap-2">
    @if($user->avatar)
    <img src="{{ asset($user->avatar) }}" 
         alt="{{ $user->name }}" 
         class="h-8 w-8 rounded-full object-cover">
    @else
    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    @endif
    <span>{{ $user->name }}</span>
</div>