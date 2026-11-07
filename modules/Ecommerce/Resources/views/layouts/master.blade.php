<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $information->name ?? 'Tienda' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="{{ asset('porto-ecommerce/assets/js/jquery.min.js') }}"></script>
    @if (file_exists(public_path('theme/custom_styles_ecommerce.css')))
        <link rel="stylesheet" href="{{ asset('theme/custom_styles_ecommerce.css') }}" />
    @endif
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #0f172a;
            --accent: #2563eb;
            --accent-hover: #1d4ed8;
            --accent-light: #eff6ff;
            --bg: #f8fafc;
            --white: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --text-light: #94a3b8;
            --green: #22c55e;
            --radius: 14px;
            --radius-sm: 8px;
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 40px rgba(0,0,0,0.14);
            --shadow-xl: 0 20px 60px rgba(0,0,0,0.18);
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; color: inherit; }
        img { display: block; }

        /* ─── TOPBAR ─── */
        .topbar {
            background: var(--primary);
            padding: 8px 0;
            font-size: 12px;
            color: #475569;
        }
        .topbar-inner {
            max-width: 1320px; margin: 0 auto; padding: 0 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .topbar-left, .topbar-right { display: flex; align-items: center; gap: 20px; }
        .topbar-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #475569; }
        .topbar-item i { font-size: 11px; color: #334155; }
        .topbar-right a { color: #475569; font-size: 12px; transition: color .15s; display: flex; align-items: center; gap: 5px; }
        .topbar-right a:hover { color: #94a3b8; }

        /* ─── HEADER ─── */
        .site-header {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 600;
            box-shadow: var(--shadow-sm);
        }
        .header-inner {
            max-width: 1320px; margin: 0 auto; padding: 0 24px;
            display: flex; align-items: center; gap: 16px; height: 72px;
        }
        .site-logo { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .site-logo img { max-height: 46px; width: auto; }
        .logo-text { font-size: 22px; font-weight: 800; color: var(--primary); letter-spacing: -0.8px; }
        .logo-dot { color: var(--accent); }

        /* Search */
        .search-wrap { flex: 1; max-width: 520px; position: relative; }
        .search-inner { display: flex; align-items: center; background: var(--bg); border: 1.5px solid var(--border); border-radius: 50px; overflow: hidden; transition: border-color .2s, box-shadow .2s; }
        .search-inner:focus-within { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,.1); background: #fff; }
        .search-inner input { flex: 1; padding: 11px 16px; border: none; outline: none; background: transparent; font-size: 14px; font-family: 'Inter', sans-serif; color: var(--text); }
        .search-inner input::placeholder { color: var(--text-light); }
        .search-btn { background: var(--accent); border: none; color: #fff; padding: 0 18px; height: 44px; cursor: pointer; font-size: 13px; transition: background .2s; display: flex; align-items: center; gap: 6px; font-family: 'Inter', sans-serif; font-weight: 600; white-space: nowrap; }
        .search-btn:hover { background: var(--accent-hover); }
        .search-dropdown { position: absolute; top: calc(100% + 8px); left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow-xl); z-index: 9999; max-height: 380px; overflow-y: auto; }
        .search-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; color: var(--text); border-bottom: 1px solid #f8fafc; transition: background .15s; cursor: pointer; }
        .search-item:hover { background: #f8fafc; }
        .search-item:last-child { border-bottom: none; }
        .search-item img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border); flex-shrink: 0; }
        .search-item .si-info { flex: 1; }
        .search-item .si-name { font-size: 13px; font-weight: 500; line-height: 1.4; }
        .search-item .si-price { font-size: 13px; font-weight: 700; color: var(--accent); margin-top: 2px; }

        /* Header right */
        .header-right { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-shrink: 0; }
        .header-phone { display: flex; align-items: center; gap: 7px; font-size: 13px; font-weight: 600; color: var(--text); white-space: nowrap; padding: 8px 12px; border-radius: 50px; border: 1px solid var(--border); background: var(--bg); }
        .header-phone i { color: var(--green); font-size: 17px; }
        .btn-cart { display: flex; align-items: center; gap: 8px; background: var(--accent); color: #fff; padding: 10px 20px; border-radius: 50px; font-size: 13px; font-weight: 600; transition: all .2s; white-space: nowrap; position: relative; }
        .btn-cart:hover { background: var(--accent-hover); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,.3); }
        .btn-cart .badge { background: #ef4444; color: #fff; border-radius: 50%; width: 19px; height: 19px; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .header-session { display: flex; align-items: center; }

        /* ─── NAV ─── */
        .site-nav { background: var(--primary); }
        .nav-inner { max-width: 1320px; margin: 0 auto; padding: 0 24px; display: flex; align-items: center; }
        .nav-link { color: #64748b; padding: 13px 16px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 7px; border-radius: 6px; transition: all .2s; white-space: nowrap; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); }
        .nav-link.active { color: #fff; }
        .nav-cats { position: relative; }
        .nav-cats-toggle { color: #64748b; padding: 13px 16px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 7px; cursor: pointer; border-radius: 6px; transition: all .2s; user-select: none; }
        .nav-cats-toggle:hover, .nav-cats.open .nav-cats-toggle { color: #fff; background: rgba(255,255,255,0.1); }
        .chevron { font-size: 10px; transition: transform .25s; margin-left: 2px; }
        .nav-cats.open .chevron { transform: rotate(180deg); }
        .nav-cats-menu { display: none; position: absolute; top: calc(100% + 6px); left: 0; background: #fff; border-radius: var(--radius); box-shadow: var(--shadow-xl); min-width: 240px; z-index: 9999; padding: 8px; border: 1px solid var(--border); }
        .nav-cats.open .nav-cats-menu { display: block; animation: fadeDown .15s ease; }
        @keyframes fadeDown { from { opacity:0; transform: translateY(-6px); } to { opacity:1; transform: translateY(0); } }
        .nav-cats-menu a { display: flex; align-items: center; gap: 10px; padding: 10px 14px; color: var(--text); font-size: 13.5px; font-weight: 500; border-radius: 8px; transition: all .15s; }
        .nav-cats-menu a:hover { background: var(--accent-light); color: var(--accent); }
        .nav-cats-menu a i { width: 16px; color: var(--text-light); font-size: 12px; }
        .nav-cats-menu .divider { height: 1px; background: var(--border); margin: 6px 4px; }
        .nav-badge { background: #ef4444; color: #fff; font-size: 9px; font-weight: 800; padding: 2px 5px; border-radius: 4px; margin-left: 4px; }

        /* ─── LAYOUT ─── */
        .site-main { max-width: 1320px; margin: 28px auto; padding: 0 24px; }
        .content-grid { display: grid; grid-template-columns: 252px 1fr; gap: 24px; align-items: start; }

        /* ─── SIDEBAR ─── */
        .sidebar { position: sticky; top: 90px; display: flex; flex-direction: column; gap: 16px; }
        .sidebar-card { background: #fff; border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; box-shadow: var(--shadow-xs); }
        .sidebar-head { background: var(--primary); color: #fff; padding: 13px 18px; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; display: flex; align-items: center; gap: 8px; }
        .sidebar-cats { list-style: none; }
        .sidebar-cats li a { display: flex; align-items: center; justify-content: space-between; padding: 11px 18px; color: var(--text-muted); font-size: 13.5px; border-bottom: 1px solid #f8fafc; transition: all .15s; }
        .sidebar-cats li a:hover { color: var(--accent); background: var(--accent-light); padding-left: 22px; }
        .sidebar-cats li.active a { color: var(--accent); background: var(--accent-light); font-weight: 600; border-left: 3px solid var(--accent); padding-left: 15px; }
        .sidebar-cats li a .cat-name { display: flex; align-items: center; gap: 8px; }
        .sidebar-cats li a i { font-size: 11px; color: var(--text-light); }
        .sidebar-cats li a .cat-arrow { font-size: 10px; color: var(--text-light); }
        .sidebar-recent { padding: 16px 18px; display: flex; flex-direction: column; gap: 12px; }
        .recent-item { display: flex; gap: 10px; color: var(--text); transition: opacity .15s; }
        .recent-item:hover { opacity: .8; }
        .recent-item img { width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--border); flex-shrink: 0; }
        .recent-item .ri-name { font-size: 12px; font-weight: 500; line-height: 1.4; color: var(--text); }
        .recent-item .ri-price { font-size: 13px; font-weight: 700; color: var(--accent); margin-top: 4px; }

        /* ─── BANNER SLIDER ─── */
        .banner-slider { border-radius: var(--radius); overflow: hidden; margin-bottom: 24px; position: relative; min-height: 300px; }
        .banner-slide { display: none; position: relative; min-height: 300px; background-size: cover; background-position: center; }
        .banner-slide.active { display: flex; align-items: center; }
        .banner-overlay { position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,.7) 0%, rgba(0,0,0,.35) 55%, rgba(0,0,0,.1) 100%); }
        .banner-slide-content { position: relative; z-index: 2; padding: 52px 56px; max-width: 580px; }
        .banner-slide-content .tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(37,99,235,.4); color: #93c5fd; font-size: 11px; font-weight: 700; padding: 5px 12px; border-radius: 50px; border: 1px solid rgba(147,197,253,.3); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px; }
        .banner-slide-content h2 { font-size: 36px; font-weight: 900; color: #fff; line-height: 1.15; margin-bottom: 12px; letter-spacing: -0.5px; text-shadow: 0 2px 12px rgba(0,0,0,.4); }
        .banner-slide-content p { font-size: 15px; color: rgba(255,255,255,.85); margin-bottom: 28px; line-height: 1.6; }
        .banner-slide-content .btn-banner { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: var(--accent); padding: 13px 30px; border-radius: 50px; font-size: 14px; font-weight: 700; transition: all .2s; box-shadow: 0 4px 20px rgba(0,0,0,.25); }
        .banner-slide-content .btn-banner:hover { background: var(--accent); color: #fff; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(37,99,235,.5); }
        .banner-default { padding: 52px; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 55%, #2563eb 100%); min-height: 300px; }
        .banner-default-text .eyebrow { font-size: 12px; font-weight: 700; color: #93c5fd; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; display: flex; align-items: center; gap: 6px; }
        .banner-default-text h2 { font-size: 36px; font-weight: 900; color: #fff; letter-spacing: -0.5px; margin-bottom: 10px; line-height: 1.15; }
        .banner-default-text p { font-size: 14px; color: rgba(255,255,255,.65); margin-bottom: 28px; }
        .banner-default-text .btn-banner { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: var(--accent); padding: 13px 30px; border-radius: 50px; font-size: 14px; font-weight: 700; transition: all .2s; }
        .banner-default-text .btn-banner:hover { background: var(--accent); color: #fff; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,.4); }
        .banner-default-img { font-size: 120px; opacity: .1; color: #fff; }
        .banner-dots { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); display: flex; gap: 6px; z-index: 3; }
        .banner-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,.45); cursor: pointer; transition: all .25s; }
        .banner-dot.active { background: #fff; width: 24px; border-radius: 4px; }

        /* ─── PROMO STRIP ─── */
        .promo-strip { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px; }
        .promo-card { background: #fff; border-radius: var(--radius-sm); border: 1px solid var(--border); padding: 16px 18px; display: flex; align-items: center; gap: 12px; transition: box-shadow .2s; }
        .promo-card:hover { box-shadow: var(--shadow); }
        .promo-card .pc-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .promo-card .pc-icon.blue { background: #eff6ff; color: var(--accent); }
        .promo-card .pc-icon.green { background: #f0fdf4; color: #16a34a; }
        .promo-card .pc-icon.orange { background: #fff7ed; color: #ea580c; }
        .promo-card .pc-title { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
        .promo-card .pc-sub { font-size: 12px; color: var(--text-muted); }

        /* ─── SECTION HEADER ─── */
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--border); }
        .section-label { font-size: 18px; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: 10px; }
        .section-label .sl-bar { width: 4px; height: 22px; background: var(--accent); border-radius: 4px; }
        .section-count { font-size: 12px; color: var(--text-muted); background: var(--bg); padding: 5px 14px; border-radius: 50px; border: 1px solid var(--border); font-weight: 500; }

        /* ─── PRODUCTS GRID ─── */
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 18px; }

        /* ─── PAGINATION ─── */
        .pagination-wrap { margin-top: 36px; display: flex; justify-content: center; }
        .pagination { display: flex; gap: 4px; list-style: none; flex-wrap: wrap; justify-content: center; }
        .pagination li > a, .pagination li > span { display: flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 12px; border: 1px solid var(--border); border-radius: 10px; font-size: 13px; font-weight: 500; color: var(--text); transition: all .15s; background: #fff; }
        .pagination li > a:hover { background: var(--accent); color: #fff; border-color: var(--accent); }
        .pagination li.active > span { background: var(--accent); color: #fff; border-color: var(--accent); font-weight: 700; }
        .pagination li.disabled > span { color: var(--text-light); background: var(--bg); }

        /* ─── FOOTER ─── */
        .site-footer { background: var(--primary); margin-top: 72px; }
        .footer-top { max-width: 1320px; margin: 0 auto; padding: 56px 24px 40px; display: grid; grid-template-columns: 2.2fr 1fr 1fr 1.2fr; gap: 48px; }
        .footer-brand .fb-logo { font-size: 22px; font-weight: 800; color: #fff; letter-spacing: -0.5px; margin-bottom: 12px; }
        .footer-brand .fb-desc { color: #475569; font-size: 13px; line-height: 1.8; max-width: 270px; margin-bottom: 20px; }
        .footer-brand .fb-social { display: flex; gap: 8px; }
        .footer-brand .fb-social a { width: 36px; height: 36px; border-radius: 8px; background: #1e293b; color: #64748b; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: all .2s; }
        .footer-brand .fb-social a:hover { background: var(--accent); color: #fff; }
        .footer-col h5 { color: #fff; font-size: 12px; font-weight: 700; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 0.8px; }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .footer-col ul li a { color: #475569; font-size: 13px; transition: color .15s; display: flex; align-items: center; gap: 8px; }
        .footer-col ul li a:hover { color: #94a3b8; }
        .footer-col ul li a i { width: 14px; font-size: 11px; }
        .footer-contact-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; }
        .footer-contact-item i { color: #475569; font-size: 14px; margin-top: 1px; flex-shrink: 0; }
        .footer-contact-item span { color: #475569; font-size: 13px; line-height: 1.5; }
        .footer-contact-item a { color: #475569; font-size: 13px; transition: color .15s; }
        .footer-contact-item a:hover { color: #94a3b8; }
        .footer-bottom { border-top: 1px solid #1e293b; }
        .footer-bottom-inner { max-width: 1320px; margin: 0 auto; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
        .footer-bottom-inner span { color: #334155; font-size: 12px; }
        .footer-bottom-inner .fb-cards { display: flex; gap: 6px; }
        .footer-bottom-inner .fb-cards span { background: #1e293b; color: #475569; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 5px; }

        /* ─── WHATSAPP ─── */
        .wa-float { position: fixed; bottom: 28px; right: 28px; background: var(--green); color: #fff; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 4px 24px rgba(34,197,94,.45); z-index: 999; transition: transform .25s; }
        .wa-float:hover { transform: scale(1.12); color: #fff; }
        .wa-float .wa-tooltip { position: absolute; right: 68px; background: #fff; color: var(--text); font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 8px; white-space: nowrap; box-shadow: var(--shadow); opacity: 0; pointer-events: none; transition: opacity .2s; }
        .wa-float:hover .wa-tooltip { opacity: 1; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 1024px) { .footer-top { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 960px) { .content-grid { grid-template-columns: 1fr; } .sidebar { display: none; } .promo-strip { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 640px) { .header-phone { display: none; } .products-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; } .topbar { display: none; } .promo-strip { grid-template-columns: 1fr; } .banner-default { flex-direction: column; text-align: center; } .footer-top { grid-template-columns: 1fr; gap: 28px; } .footer-bottom-inner { flex-direction: column; gap: 8px; } }
    </style>
</head>
<body>
@php
    $nav_cats = \DB::connection('tenant')->table('tags')->select('id','name')->get();
@endphp

{{-- TOPBAR --}}
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-left">
            <div class="topbar-item"><i class="fas fa-map-marker-alt"></i> Lima, Perú</div>
            <div class="topbar-item"><i class="fas fa-shipping-fast"></i> Envíos a todo el país</div>
        </div>
        <div class="topbar-right">
            <a href="{{ route('tenant_ecommerce_login') }}"><i class="fas fa-user"></i> Mi cuenta</a>
            <a href="{{ route('tenant_detail_cart') }}"><i class="fas fa-shopping-bag"></i> Mi carrito</a>
        </div>
    </div>
</div>

{{-- HEADER --}}
<header class="site-header">
    <div class="header-inner">
        <a href="{{ route('tenant.ecommerce.index') }}" class="site-logo">
            @if(isset($information) && $information->logo)
                <img src="{{ asset('storage/uploads/logos/'.$information->logo) }}" alt="{{ $information->name ?? 'Logo' }}">
            @else
                <span class="logo-text">{{ $information->name ?? 'Tienda' }}<span class="logo-dot">.</span></span>
            @endif
        </a>

        <div class="search-wrap" id="header_bar">
            <div class="search-inner">
                <input type="text" placeholder="¿Qué estás buscando?" v-model="value" v-on:keyup="autoComplete" />
                <button class="search-btn"><i class="fas fa-search"></i> Buscar</button>
            </div>
            <div class="search-dropdown" v-if="results.length > 0">
                <a class="search-item" v-for="result in results" :href="'/ecommerce/item/' + result.id">
                    <img :src="result.image_url_small" alt="">
                    <div class="si-info">
                        <div class="si-name">@{{ result.description }}</div>
                        <div class="si-price">S/ @{{ result.sale_unit_price }}</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="header-right">
            @if(isset($information) && $information->information_contact_phone)
            <div class="header-phone">
                <i class="fab fa-whatsapp"></i>
                {{ $information->information_contact_phone }}
            </div>
            @endif
            <a href="{{ route('tenant_detail_cart') }}" class="btn-cart">
                <i class="fas fa-shopping-cart"></i>
                Carrito
                <span class="badge cart-count">0</span>
            </a>
            <div class="header-session">
                @include('ecommerce::partials.headers.session')
            </div>
        </div>
    </div>
</header>

{{-- NAV --}}
<nav class="site-nav">
    <div class="nav-inner">
        <a href="{{ route('tenant.ecommerce.index') }}" class="nav-link {{ !request('tagid') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Inicio
        </a>
        <div class="nav-cats" id="navCats">
            <div class="nav-cats-toggle" onclick="toggleNavCats()">
                <i class="fas fa-grip-horizontal"></i>
                Categorías
                <i class="fas fa-chevron-down chevron"></i>
            </div>
            <div class="nav-cats-menu">
                <a href="{{ route('tenant.ecommerce.index') }}">
                    <i class="fas fa-border-all"></i> Todos los productos
                </a>
                <div class="divider"></div>
                @foreach($nav_cats as $item)
                <a href="{{ route('tenant.ecommerce.category', ['tagid' => $item->id]) }}">
                    <i class="fas fa-tag"></i> {{ $item->name }}
                </a>
                @endforeach
            </div>
        </div>
        <a href="{{ route('tenant_detail_cart') }}" class="nav-link">
            <i class="fas fa-bolt"></i> Ofertas
        </a>
        <a href="{{ route('tenant_detail_cart') }}" class="nav-link">
            <i class="fas fa-fire"></i> Lo más vendido
        </a>
    </div>
</nav>

{{-- MAIN --}}
<main class="site-main">
    @php $tagid = Request::segment(3); @endphp
    <div class="content-grid">

        {{-- SIDEBAR --}}
        <aside class="sidebar">
            <div class="sidebar-card">
                <div class="sidebar-head"><i class="fas fa-layer-group"></i> Categorías</div>
                <ul class="sidebar-cats">
                    <li class="{{ !request('tagid') ? 'active' : '' }}">
                        <a href="{{ route('tenant.ecommerce.index') }}">
                            <span class="cat-name"><i class="fas fa-border-all"></i> Todos</span>
                            <i class="fas fa-chevron-right cat-arrow"></i>
                        </a>
                    </li>
                    @foreach($nav_cats as $item)
                    <li class="{{ request('tagid') == $item->id ? 'active' : '' }}">
                        <a href="{{ route('tenant.ecommerce.category', ['tagid' => $item->id]) }}">
                            <span class="cat-name"><i class="fas fa-tag"></i> {{ $item->name }}</span>
                            <i class="fas fa-chevron-right cat-arrow"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            @if(isset($records) && $records->count() > 0)
            <div class="sidebar-card">
                <div class="sidebar-head"><i class="fas fa-star"></i> Destacados</div>
                <div class="sidebar-recent">
                    @foreach($records->take(5) as $data)
                        @if($data->apply_store)
                        <a href="/ecommerce/item/{{ $data->id }}" class="recent-item">
                            <img src="{{ asset('storage/uploads/items/'.$data->image) }}" alt="">
                            <div>
                                <div class="ri-name">{{ \Illuminate\Support\Str::limit($data->description, 36) }}</div>
                                <div class="ri-price">S/ {{ number_format($data->sale_unit_price, 2) }}</div>
                            </div>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- CTA Contacto --}}
            @if(isset($information) && $information->information_contact_phone)
            <div class="sidebar-card" style="background: linear-gradient(135deg,#0f172a,#1e3a5f); border:none;">
                <div style="padding:20px 18px; text-align:center;">
                    <div style="font-size:28px; color:#22c55e; margin-bottom:10px;"><i class="fab fa-whatsapp"></i></div>
                    <div style="color:#fff; font-size:13px; font-weight:700; margin-bottom:4px;">¿Necesitas ayuda?</div>
                    <div style="color:#64748b; font-size:12px; margin-bottom:14px;">Escríbenos por WhatsApp</div>
                    <a href="https://wa.me/51{{ $information->information_contact_phone }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;background:#22c55e;color:#fff;padding:9px 20px;border-radius:50px;font-size:12px;font-weight:700;">
                        <i class="fab fa-whatsapp"></i> Chatear ahora
                    </a>
                </div>
            </div>
            @endif
        </aside>

        {{-- CONTENT --}}
        <div>
            @if(!$tagid)
            {{-- BANNER --}}
            @php
                $promotions = \DB::select('select * from tenancy_sistemaventa.promotions where status=1 and apply_restaurant=0');
            @endphp
            @if(count($promotions) > 0)
            <div class="banner-slider" id="bannerSlider">
                @foreach($promotions as $i => $promo)
                <div class="banner-slide {{ $i === 0 ? 'active' : '' }}" style="background-image:url('{{ asset('storage/uploads/promotions/'.$promo->image) }}'); background-size:cover; background-position:center;">
                    <div class="banner-overlay"></div>
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
                @if(count($promotions) > 1)
                <div class="banner-dots">
                    @foreach($promotions as $i => $promo)
                    <div class="banner-dot {{ $i === 0 ? 'active' : '' }}" onclick="goSlide({{ $i }})"></div>
                    @endforeach
                </div>
                @endif
            </div>
            @else
            <div class="banner-slider">
                <div class="banner-default">
                    <div class="banner-default-text">
                        <div class="eyebrow"><i class="fas fa-bolt"></i> Bienvenido a nuestra tienda</div>
                        <h2>Los mejores<br>productos al mejor precio</h2>
                        <p>Calidad garantizada · Envíos rápidos · Pagos seguros</p>
                        <a href="{{ route('tenant.ecommerce.index') }}" class="btn-banner">
                            Ver productos <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="banner-default-img"><i class="fas fa-store"></i></div>
                </div>
            </div>
            @endif

            {{-- PROMO STRIP --}}
            <div class="promo-strip">
                <div class="promo-card">
                    <div class="pc-icon blue"><i class="fas fa-shipping-fast"></i></div>
                    <div>
                        <div class="pc-title">Envío rápido</div>
                        <div class="pc-sub">A todo el país</div>
                    </div>
                </div>
                <div class="promo-card">
                    <div class="pc-icon green"><i class="fas fa-shield-alt"></i></div>
                    <div>
                        <div class="pc-title">Compra segura</div>
                        <div class="pc-sub">Pagos protegidos</div>
                    </div>
                </div>
                <div class="promo-card">
                    <div class="pc-icon orange"><i class="fas fa-headset"></i></div>
                    <div>
                        <div class="pc-title">Soporte 24/7</div>
                        <div class="pc-sub">Siempre disponibles</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- SECTION HEADER --}}
            <div class="section-header">
                <div class="section-label">
                    <div class="sl-bar"></div>
                    {{ request('tagid') ? ($nav_cats->where('id', request('tagid'))->first()->name ?? 'Productos') : 'Todos los productos' }}
                </div>
                <span class="section-count"><i class="fas fa-box" style="font-size:11px;"></i> {{ $dataPaginate->total() }} productos</span>
            </div>

            <div class="products-grid">
                @include('ecommerce::layouts.partials_ecommerce.list_products')
            </div>

            <div class="pagination-wrap">
                {{ $dataPaginate->links() }}
            </div>
        </div>
    </div>
</main>

{{-- FOOTER --}}
<footer class="site-footer">
    <div class="footer-top">
        <div class="footer-brand">
            <div class="fb-logo">{{ $information->name ?? 'Tienda Virtual' }}</div>
            <div class="fb-desc">Tu tienda de confianza. Encuentra los mejores productos con garantía de calidad y envíos a todo el Perú.</div>
            <div class="fb-social">
                <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                @if(isset($information) && $information->information_contact_phone)
                <a href="https://wa.me/51{{ $information->information_contact_phone }}" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                @endif
            </div>
        </div>
        <div class="footer-col">
            <h5>Categorías</h5>
            <ul>
                <li><a href="{{ route('tenant.ecommerce.index') }}"><i class="fas fa-border-all"></i> Todos</a></li>
                @foreach($nav_cats as $item)
                <li><a href="{{ route('tenant.ecommerce.category', ['tagid' => $item->id]) }}">
                    <i class="fas fa-chevron-right"></i> {{ $item->name }}
                </a></li>
                @endforeach
            </ul>
        </div>
        <div class="footer-col">
            <h5>Mi Cuenta</h5>
            <ul>
                <li><a href="{{ route('tenant_detail_cart') }}"><i class="fas fa-shopping-cart"></i> Ver Carrito</a></li>
                <li><a href="{{ route('tenant_ecommerce_login') }}"><i class="fas fa-user"></i> Iniciar Sesión</a></li>
                <li><a href="{{ route('tenant.ecommerce.index') }}"><i class="fas fa-box"></i> Mis pedidos</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>Contacto</h5>
            @if(isset($information) && $information->information_contact_phone)
            <div class="footer-contact-item">
                <i class="fab fa-whatsapp"></i>
                <a href="https://wa.me/51{{ $information->information_contact_phone }}">{{ $information->information_contact_phone }}</a>
            </div>
            @endif
            <div class="footer-contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Lima, Perú</span>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <span>© {{ date('Y') }} {{ $information->name ?? 'Tienda Virtual' }}. Todos los derechos reservados.</span>
            <div class="fb-cards">
                <span>VISA</span>
                <span>MC</span>
                <span>YAPE</span>
                <span>PLIN</span>
            </div>
        </div>
    </div>
</footer>

@if(isset($information) && $information->information_contact_phone)
<a href="https://wa.me/51{{ $information->information_contact_phone }}" target="_blank" class="wa-float" title="WhatsApp">
    <i class="fab fa-whatsapp"></i>
    <span class="wa-tooltip">Escríbenos</span>
</a>
@endif

<script src="{{ asset('porto-ecommerce/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('porto-ecommerce/assets/js/vue.min.js') }}"></script>
<script>
// Search
new Vue({
    el: '#header_bar',
    data: { value: '', suggestions: [], results: [] },
    created() { this.getItems(); },
    methods: {
        autoComplete() {
            this.results = this.value
                ? this.suggestions.filter(o => o.description.toUpperCase().includes(this.value.toUpperCase())).slice(0, 8)
                : [];
        },
        getItems() {
            fetch('/ecommerce/items_bar').then(r => r.json()).then(d => { this.suggestions = d.data || []; });
        }
    }
});

// Cart count
function updateCartCount() {
    let arr = JSON.parse(localStorage.getItem('products_cart') || '[]');
    document.querySelectorAll('.cart-count').forEach(el => el.textContent = arr.length);
}
$(function() { updateCartCount(); });

// Nav dropdown
function toggleNavCats() { document.getElementById('navCats').classList.toggle('open'); }
document.addEventListener('click', function(e) {
    let el = document.getElementById('navCats');
    if (el && !el.contains(e.target)) el.classList.remove('open');
});

// Banner slider
let currentSlide = 0;
const slides = document.querySelectorAll('.banner-slide');
const dots = document.querySelectorAll('.banner-dot');
function goSlide(n) {
    slides.forEach((s,i) => { s.classList.toggle('active', i===n); });
    dots.forEach((d,i) => { d.classList.toggle('active', i===n); });
    currentSlide = n;
}
if (slides.length > 1) {
    setInterval(() => { goSlide((currentSlide + 1) % slides.length); }, 5000);
}
</script>
@stack('scripts')
</body>
</html>