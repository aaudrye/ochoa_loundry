@php($o = $order)
<div class="card">
    <div class="row">
        <div>
            <b>#{{ $o->code }}</b>
            <div class="mu">{{ $o->service_label }} · @if ($o->weight_kg > 0){{ $o->weight_kg + 0 }} kg · @rp($o->total)@else berat ditimbang kurir @endif</div>
        </div>
        @include('partials.badge', ['order' => $o])
    </div>
    <div class="st">
        @foreach (['Dijemput', 'Payment', 'Cuci', 'Siap', 'Antar'] as $j => $t)
            <i class="{{ $j < $o->status->progress() ? 'd' : '' }}">{{ $t }}</i>
        @endforeach
    </div>
    @include('partials.courier-card', ['order' => $o])

    @if ($o->status->value === 3)
        <a class="btn w" style="display:block;text-align:center;text-decoration:none" href="{{ route('customer.orders.payment', $o) }}">Bayar @rp($o->total)</a>
    @elseif ($o->status->value === 7)
        @if ($o->delivery_slot)
            <span class="bd o1">Jadwal antar: {{ $o->delivery_slot }} · menunggu kurir</span>
        @else
            <a class="btn w" style="display:block;text-align:center;text-decoration:none" href="{{ route('customer.orders.show', $o) }}#jadwal">Atur Jadwal Pengantaran</a>
        @endif
    @endif

    @unless ($detail ?? false)
        <a class="btn s l" style="margin-top:8px;text-decoration:none;display:inline-block" href="{{ route('customer.orders.show', $o) }}">Lihat Detail</a>
    @endunless
</div>