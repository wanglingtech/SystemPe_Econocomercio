@foreach ($dataPaginate as $item)
<div class="product-card-wrap">
    <a href="/ecommerce/item/{{ $item->id }}" class="product-card">
        <div class="img-wrap">
            <img src="{{ $item->image == 'imagen-no-disponible.jpg' ? asset('logo/imagen-no-disponible.jpg') : asset('storage/uploads/items/'.$item->image) }}" alt="{{ $item->description }}">
            @if(json_encode($item->is_new) == 1)
                <span class="badge-new">Nuevo</span>
            @endif
            @if(stock($item, $configuration))
                <span class="badge-out">Agotado</span>
            @endif
        </div>
        <div class="card-body">
            <div class="card-sku">{{ $item->internal_id }}</div>
            <div class="card-name">{{ $item->description }}</div>
            <div class="card-price">{{ $item->currency_type['symbol'] }} {{ number_format($item->sale_unit_price, 2) }}</div>
        </div>
    </a>
    @if(!stock($item, $configuration))
    <button class="btn-add-cart" data-product="{{ json_encode($item) }}">
        <i class="fas fa-shopping-cart"></i> Agregar al carrito
    </button>
    @endif
</div>
@endforeach

<?php
function stock($item, $config) {
    if($config) {
        $stock = 0;
        foreach ($item->warehouses as $value) { $stock += $value->stock; }
        return ($stock > 0) ? false : true;
    }
    return false;
}
?>

<style>
.products-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)) !important;
    gap: 20px !important;
}
.product-card-wrap {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.25s;
    display: flex;
    flex-direction: column;
}
.product-card-wrap:hover {
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: #2563eb;
}
.product-card { text-decoration: none; color: inherit; display: flex; flex-direction: column; flex: 1; }
.product-card .img-wrap {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f9fafb;
    position: relative;
}
.product-card .img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.4s;
}
.product-card-wrap:hover .img-wrap img { transform: scale(1.06); }
.badge-new, .badge-out {
    position: absolute; top: 10px; left: 10px;
    padding: 4px 10px; border-radius: 50px;
    font-size: 11px; font-weight: 700;
}
.badge-new { background: #2563eb; color: #fff; }
.badge-out { background: #ef4444; color: #fff; }
.card-body { padding: 14px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
.card-sku { font-size: 11px; color: #9ca3af; font-weight: 500; }
.card-name { font-size: 14px; font-weight: 600; color: #111827; line-height: 1.4; flex: 1; }
.card-price { font-size: 18px; font-weight: 800; color: #2563eb; margin-top: 6px; }
.btn-add-cart {
    margin: 0 14px 14px;
    background: #111827;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    width: calc(100% - 28px);
    transition: background 0.2s;
    font-family: 'Inter', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-add-cart:hover { background: #2563eb; }
</style>

<script>
$(function() {
    $('.btn-add-cart').on('click', function() {
        let product = $(this).data('product');
        let array = JSON.parse(localStorage.getItem('products_cart') || '[]');
        let exists = array.find(x => x.id == product.id);
        if (!exists) {
            array.push(product);
            localStorage.setItem('products_cart', JSON.stringify(array));
        }
        document.querySelectorAll('.cart-count').forEach(el => el.textContent = array.length);
        $(this).text('✓ Agregado').css('background', '#16a34a');
        setTimeout(() => {
            $(this).html('<i class="fas fa-shopping-cart"></i> Agregar al carrito').css('background', '#111827');
        }, 1500);
    });
});
</script>