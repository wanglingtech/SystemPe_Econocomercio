<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($information) ? $information->name : 'Tienda Virtual' }}</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('electro/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('electro/css/slick.css') }}"/>
    <link rel="stylesheet" href="{{ asset('electro/css/slick-theme.css') }}"/>
    <link rel="stylesheet" href="{{ asset('electro/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('electro/css/style.css') }}"/>
    <style>
/* CHECKOUT MEJORADO */
.section { padding: 30px 0; }
.cart-table-container { background: #fff; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.07); padding: 25px; margin-bottom: 25px; }
.cart-table-container p { font-weight: 700; font-size: 16px; color: #1e293b; margin-bottom: 15px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px; }
.table-cart thead { background: #1e293b; }
.table-cart thead th { color: #fff !important; padding: 14px 12px !important; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; border: none !important; }
.table-cart tbody td { padding: 16px 12px !important; vertical-align: middle !important; border-bottom: 1px solid #f1f5f9 !important; }
.table-cart tbody tr:hover { background: #f8fafc; }
.table-cart .product-col { display: flex; align-items: center; gap: 15px; }
.table-cart .product-image { width: 70px; height: 70px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; flex-shrink: 0; }
.table-cart .product-image img { width: 100%; height: 100%; object-fit: cover; }
.table-cart .product-title a { font-weight: 600; color: #1e293b; font-size: 14px; text-decoration: none; }
.table-cart tfoot td { padding: 16px 12px !important; background: #f8fafc; }
.btn-outline-secondary { border: 1px solid #e2e8f0 !important; color: #475569 !important; border-radius: 8px !important; padding: 8px 16px !important; font-size: 13px !important; }
.btn-outline-secondary:hover { background: #f1f5f9 !important; }
.btn-outline-danger { border-radius: 6px !important; }

/* RESUMEN LATERAL */
.cart-summary { background: #fff; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.07); padding: 25px; margin-bottom: 20px; }
.cart-summary h3 { font-size: 16px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px; }
.table-totals td { padding: 10px 0 !important; border: none !important; color: #475569; font-size: 14px; }
.table-totals tfoot td { font-weight: 700 !important; font-size: 16px !important; color: #1e293b !important; border-top: 2px solid #f1f5f9 !important; padding-top: 14px !important; }
.checkout-methods .btn { border-radius: 8px !important; padding: 12px !important; font-weight: 600 !important; margin-bottom: 10px !important; font-size: 14px !important; }
.checkout-methods .btn-primary { background: #2563eb !important; border: none !important; }
.checkout-methods .btn-primary:hover { background: #1d4ed8 !important; }

/* FORMULARIOS */
.form-group label { font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 6px; display: block; }
.form-control { border-radius: 8px !important; border: 1px solid #e2e8f0 !important; padding: 10px 14px !important; font-size: 14px !important; }
.form-control:focus { border-color: #2563eb !important; box-shadow: 0 0 0 3px rgba(37,99,235,0.1) !important; }
</style>
    <script src="{{ asset('porto-ecommerce/assets/js/culqi_v3.js') }}"></script>
    <script src="{{ asset('porto-ecommerce/assets/js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('porto-ecommerce/assets/js/moment.min.js') }}"></script>
</head>
<body>
<header>
    <div id="top-header">
        <div class="container">
            <ul class="header-links pull-left">
                <li><a href="#"><i class="fa fa-whatsapp"></i> {{ isset($information) ? $information->information_contact_phone : '' }}</a></li>
            </ul>
            <ul class="header-links pull-right">
                @include('ecommerce::partials.headers.session')
            </ul>
        </div>
    </div>
    <div id="header">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="header-logo">
                        <a href="{{ url('ecommerce') }}" class="logo">
                            @if(isset($information) && $information->logo)
                                <img src="{{ asset('storage/uploads/logos/'.$information->logo) }}" alt="Logo" style="max-height:55px;" />
                            @else
                                <img src="{{ asset('logo/tulogo.png') }}" alt="Logo" style="max-height:55px;" />
                            @endif
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="header-search">
                        <form action="{{ url('ecommerce') }}" method="GET">
                            <input class="input" name="search" placeholder="Buscar productos..." type="text" />
                            <button class="search-btn">Buscar</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-3 clearfix">
                    <div class="header-ctn">
                        <div class="dropdown">
                            <a class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-shopping-cart"></i>
                                <span>Carrito</span>
                                <div class="qty cart-count">0</div>
                            </a>
                            <div class="cart-dropdown">
                                <div class="cart-list dropdown-cart-products"></div>
                                <div class="cart-summary">
                                    <h5>TOTAL: S/ <span class="cart-total-price">0.00</span></h5>
                                </div>
                                <div class="cart-btns">
                                    <a href="{{ url('ecommerce/detail_cart') }}">Ver Carrito</a>
                                </div>
                            </div>
                        </div>
                        <div class="menu-toggle">
                            <a href="#"><i class="fa fa-bars"></i><span>Menu</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<nav id="navigation">
    <div class="container">
        <div id="responsive-nav">
            <ul class="main-nav nav navbar-nav">
                <li><a href="{{ url('ecommerce') }}"><i class="fa fa-home"></i> Inicio</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="section">
    <div class="container">
        @yield('content')
    </div>
</div>

<footer id="footer">
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="footer">
                        <h3 class="footer-title">{{ isset($information) ? $information->name : '' }}</h3>
                        <ul class="footer-links">
                            <li><a href="#"><i class="fa fa-phone"></i> {{ isset($information) ? $information->information_contact_phone : '' }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="footer">
                        <h3 class="footer-title">Mi Cuenta</h3>
                        <ul class="footer-links">
                            <li><a href="{{ url('ecommerce/detail_cart') }}">Ver Carrito</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="bottom-footer" class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <span class="copyright">© {{ date('Y') }} {{ isset($information) ? $information->name : 'Tienda Virtual' }}. Todos los derechos reservados.</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="{{ asset('electro/js/jquery.min.js') }}"></script>
<script src="{{ asset('electro/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('electro/js/slick.min.js') }}"></script>
<script src="{{ asset('electro/js/main.js') }}"></script>
<script src="{{ asset('porto-ecommerce/assets/js/vue.js') }}"></script>
<script src="{{ asset('porto-ecommerce/assets/js/axios.min.js') }}"></script>

<script>
function removeFromCart(id) {
    let array = JSON.parse(localStorage.getItem('products_cart') || '[]');
    array.splice(array.findIndex(x => x.id == id), 1);
    localStorage.setItem('products_cart', JSON.stringify(array));
    updateCart();
}
function updateCart() {
    let array = JSON.parse(localStorage.getItem('products_cart') || '[]');
    let total = 0;
    array.forEach(el => { total += parseFloat(el.sale_unit_price) });
    $(".cart-count").text(array.length);
    $(".cart-total-price").text(total.toFixed(2));
    $(".dropdown-cart-products").empty();
    array.forEach(el => {
        $(".dropdown-cart-products").append(`
            <div class="product-widget">
                <div class="product-img"><img src="/storage/uploads/items/${el.image_small}" alt="${el.name}"></div>
                <div class="product-body">
                    <h3 class="product-name"><a href="#">${el.name}</a></h3>
                    <h4 class="product-price">S/ ${el.sale_unit_price}</h4>
                </div>
                <button class="delete" onclick="removeFromCart(${el.id})"><i class="fa fa-close"></i></button>
            </div>`);
    });
}
$(function() { updateCart(); });
</script>

@stack('scripts')
</body>
</html>