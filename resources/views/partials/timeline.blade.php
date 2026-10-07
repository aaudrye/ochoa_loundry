<div class="tl">
    @foreach ($order->logs->reverse() as $l)
        <div>
            <b>{{ $l->message }}</b>
            <div class="mu">{{ $l->created_at->format('d/m H.i') }}</div>
        </div>
    @endforeach
</div>