@if(isset($items) && $items->count() > 0)
<div class="banner-slider" id="bannerSlider">
    @foreach($items as $i => $promo)
    <div class="banner-slide {{ $i === 0 ? 'active' : '' }}">
        <div class="banner-slide-bg" style="background-image:url('{{ asset('storage/uploads/promotions/'.$promo->image) }}')"></div>
        <div class="banner-slide-content">
            <div class="tag"><i class="fas fa-bolt"></i> Oferta especial</div>
            <h2>{{ $promo->name }}</h2>
            <p>{{ $promo->description }}</p>
            <a href="/ecommerce/item/{{ $promo->item_id }}/{{ $promo->id }}" class="btn-banner">
                Comprar ahora <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
    @endforeach
    @if($items->count() > 1)
    <div class="banner-dots" id="bannerDots">
        @foreach($items as $i => $promo)
        <div class="banner-dot {{ $i === 0 ? 'active' : '' }}" onclick="goSlide({{ $i }})"></div>
        @endforeach
    </div>
    @endif
</div>
@else
<div class="banner-slider">
    <div class="banner-default">
        <div class="banner-default-text">
            <div class="eyebrow"><i class="fas fa-bolt"></i> Bienvenido</div>
            <h2>Los mejores productos<br>al mejor precio</h2>
            <p>Calidad garantizada · Envíos rápidos · Pagos seguros</p>
            <a href="{{ route('tenant.ecommerce.index') }}" class="btn-banner">
                Ver productos <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        <div class="banner-default-img"><i class="fas fa-store"></i></div>
    </div>
</div>
@endif