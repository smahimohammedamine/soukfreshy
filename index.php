<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>SoukFreshy — Fraîcheur directe des fermes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
  <link rel="stylesheet" href="user/styles.css">
  <link rel="stylesheet" href="user/auth.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#f0faf4', 100: '#dcf5e7', 200: '#bbead1',
              300: '#87d8b0', 400: '#4dbe87', 500: '#27a163',
              600: '#1e6b3c', 700: '#1a5c33', 800: '#154a29', 900: '#0f3a1f',
            },
            accent: '#f0a500',
            warm: '#faf9f6',
          },
          fontFamily: {
            display: ['"Playfair Display"', 'serif'],
            body: ['Nunito', 'sans-serif'],
          },
        }
      }
    }
  </script>
</head>
<body class="font-body bg-warm overflow-x-hidden">

<!-- ══════════════ SPLASH ══════════════ -->
<div id="splash" class="fixed inset-0 z-[100] flex flex-col items-center justify-center overflow-hidden" style="background: linear-gradient(145deg, #0f3a1f 0%, #1e6b3c 60%, #27a163 100%);">
  <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 25% 50%, #fff 1px, transparent 1px), radial-gradient(circle at 75% 25%, #fff 1px, transparent 1px); background-size: 44px 44px;"></div>
  <div id="sp-logo" class="opacity-0 mb-6">
    <div class="w-24 h-24 rounded-full border-4 border-white/25 overflow-hidden" style="box-shadow: 0 0 60px rgba(39,161,99,0.45), 0 0 0 8px rgba(255,255,255,0.07);">
      <img src="images/logo 1.jpeg" alt="SoukFreshy" class="w-full h-full object-cover">
    </div>
  </div>
  <div id="sp-title" class="opacity-0 text-center">
    <h1 class="text-white text-4xl font-display font-bold tracking-tight">Souk<span style="color:#f0a500">Freshy</span></h1>
    <div class="w-10 h-0.5 mx-auto mt-3 mb-5" style="background:#f0a500;"></div>
    <p class="text-white/55 text-xs font-body tracking-[0.22em] uppercase">Bienvenue</p>
    <div class="mt-5 px-5 py-3.5 rounded-xl max-w-[360px] mx-auto" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.14); box-shadow:0 10px 26px rgba(0,0,0,0.2); backdrop-filter: blur(4px);">
      <p class="font-display text-[17px] leading-[1.35] text-white/95 tracking-[-0.01em]">
        "La fraîcheur des fermes algériennes, livrée avec confiance à votre table."
      </p>
      <p class="font-body text-[11px] text-white/60 mt-2.5 tracking-[0.14em] uppercase">SoukFreshy</p>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     HOME PAGE
══════════════════════════════════════════════════════════ -->
<div id="page-home" class="page hidden">

  <!-- TOP NAVBAR -->
  <nav class="home-nav fixed top-0 left-0 right-0 z-50">
    <div class="flex items-center justify-between px-5 md:px-6 py-2.5">
      <button onclick="showPage('home')" class="home-brand flex items-center gap-2.5 focus-visible:outline-none">
        <div class="w-9 h-9 rounded-full overflow-hidden border-2" style="border-color:rgba(240,165,0,0.45);">
          <img src="images/logo 1.jpeg" alt="SoukFreshy" class="w-full h-full object-cover">
        </div>
        <span class="font-display font-bold text-[1.1rem] text-white">Souk<span style="color:#f0a500;">Freshy</span></span>
      </button>
      <div class="flex items-center gap-2 md:gap-2.5">
        <a href="#" onclick="document.getElementById('welcome-section')?.scrollIntoView({behavior:'smooth'}); return false;" class="nav-link-main hidden md:inline-flex">À propos</a>
        <a href="#" onclick="document.getElementById('features-strip')?.scrollIntoView({behavior:'smooth'}); return false;" class="nav-link-main hidden md:inline-flex">Nos services</a>
        <a href="#" onclick="document.getElementById('categories-section')?.scrollIntoView({behavior:'smooth'}); return false;" class="nav-link-main hidden md:inline-flex">Catégories</a>
        <a href="#" onclick="showPage('shop'); return false;" class="nav-link-main inline-flex">Produits</a>
        <a id="auth-nav-btn" href="#" class="js-auth-trigger nav-login-btn font-body font-semibold text-sm px-4 py-1.5 rounded-lg" onclick="handleAuthNavClick(); return false;">
          Connexion
        </a>
      </div>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <section class="hero-wrap relative overflow-hidden" style="min-height: 100vh;">
    <div class="absolute inset-0">
      <img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=900&q=85" alt="Légumes frais" class="w-full h-full object-cover">
      <div class="absolute inset-0" style="background: linear-gradient(160deg, rgba(10,38,20,0.78) 0%, rgba(15,58,31,0.52) 45%, rgba(10,38,20,0.93) 100%);"></div>
      <div class="noise-overlay"></div>
    </div>
    <div class="relative z-10 flex flex-col justify-end min-h-[88vh] px-5 pb-14 max-w-lg mx-auto w-full">
      <div>
        <span class="hero-el inline-flex items-center gap-1.5 text-[11px] font-body font-semibold tracking-widest uppercase px-3 py-1.5 rounded-full border mb-5" style="background:rgba(240,165,0,0.15); color:#f0a500; border-color:rgba(240,165,0,0.3);">
          <i class="fas fa-leaf text-[9px]"></i> Fraîcheur garantie
        </span>
        <h1 class="hero-el text-white font-display font-bold leading-[1.1] mb-4" style="font-size: clamp(2.1rem, 8.5vw, 2.9rem); letter-spacing:-0.02em;">
          Fruits &amp; légumes<br><em style="color:#f0a500; font-style:italic;">frais chaque jour</em>
        </h1>
        <p class="hero-el text-white/70 font-body text-sm leading-[1.75] mb-8" style="max-width: 290px;">
          Directement des fermes algériennes à votre porte — sans intermédiaires.
        </p>
        <div class="hero-el flex justify-center">
          <button onclick="showPage('shop')" class="cta-btn inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-body font-semibold text-sm" style="background: linear-gradient(135deg, #f0a500, #d99300); color:#0f2a0f; box-shadow: 0 8px 28px rgba(240,165,0,0.4);">
            <i class="fas fa-store text-sm"></i> Commander maintenant
          </button>
        </div>
      </div>
    </div>
    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 text-white/30 animate-bounce z-10">
      <i class="fas fa-chevron-down text-xs"></i>
    </div>
  </section>

  <!-- ── WELCOME SECTION ── -->
  <section id="welcome-section" class="py-14 md:py-16" style="background:#faf9f6;">
    <div class="px-5 md:px-10 lg:px-14">
      <div class="grid grid-cols-1 md:grid-cols-[1.05fr_0.95fr] gap-5 md:gap-7 items-stretch">
        <div class="welcome-el rounded-[18px] overflow-hidden relative h-[270px] md:h-[355px]" style="box-shadow:0 10px 30px rgba(0,0,0,0.13);">
          <video id="welcome-video" class="w-full h-full object-cover" autoplay muted playsinline preload="auto">
            <source src="videos/soukfrechy.mp4" type="video/mp4">
          </video>
          <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(0,0,0,0.28) 0%, rgba(0,0,0,0.08) 38%, transparent 70%);"></div>
          <!-- Play / Pause button -->
          <button id="welcome-video-btn" onclick="toggleWelcomeVideo()" class="absolute bottom-4 right-4 z-10 w-10 h-10 rounded-full flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95" style="background:rgba(0,0,0,0.45); border:1.5px solid rgba(255,255,255,0.35); backdrop-filter:blur(6px); color:#fff;">
            <i id="welcome-video-icon" class="fas fa-pause text-[11px]"></i>
          </button>
        </div>

        <article class="welcome-el rounded-[10px] text-white p-7 md:p-9 min-h-[270px] md:min-h-[355px] flex flex-col justify-center" style="background:linear-gradient(145deg, #0f3a1f 0%, #1e6b3c 100%); box-shadow:0 10px 30px rgba(30,107,60,0.32);">
          <h2 class="font-display font-bold text-[1.7rem] md:text-[2rem] leading-[1.08] tracking-[-0.03em] mb-4">Bienvenue sur nos plateformes <span style="color:#bbead1;">SoukFreshy</span></h2>
          <p class="font-body text-[14px] md:text-[15px] leading-[1.75] text-white/90 max-w-[42ch]">
            La première plateforme algérienne qui connecte directement les agriculteurs aux consommateurs pour une expérience plus simple et plus transparente.
          </p>
          <p class="font-body text-[14px] md:text-[15px] leading-[1.75] text-white/90 max-w-[42ch] mt-2.5">
            Notre mission est de livrer des produits frais, des prix justes et une qualité locale sans intermédiaires.
          </p>
        </article>
      </div>

      <div class="welcome-el mt-7 md:mt-9 rounded-2xl p-4 md:p-5 grid grid-cols-3 gap-3 md:gap-4" style="background:#ffffff; border:1px solid #e8f5ee; box-shadow:0 8px 26px rgba(30,107,60,0.09);">
        <div class="welcome-metric rounded-xl px-3 py-3 md:py-4 text-center" style="background:#f0faf4;">
          <p class="welcome-stat-value font-display font-bold text-[1.55rem] leading-none" style="color:#1e6b3c;" data-target="500" data-prefix="" data-suffix="+">0+</p>
          <p class="font-body text-[11px] md:text-xs mt-1.5 leading-snug text-slate-500">Clients<br>satisfaits</p>
        </div>
        <div class="welcome-metric rounded-xl px-3 py-3 md:py-4 text-center" style="background:#f0faf4;">
          <p class="welcome-stat-value font-display font-bold text-[1.55rem] leading-none" style="color:#1e6b3c;" data-target="10" data-prefix="" data-suffix="+">0+</p>
          <p class="font-body text-[11px] md:text-xs mt-1.5 leading-snug text-slate-500">Wilayate<br>servies</p>
        </div>
        <div class="welcome-metric rounded-xl px-3 py-3 md:py-4 text-center" style="background:#f0faf4;">
          <p class="welcome-stat-value font-display font-bold text-[1.55rem] leading-none" style="color:#1e6b3c;" data-target="100" data-prefix="" data-suffix="%">0%</p>
          <p class="font-body text-[11px] md:text-xs mt-1.5 leading-snug text-slate-500">Produits<br>locaux</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ── NOS SERVICES ── -->
  <section id="features-strip" class="bg-white py-10">
    <div class="text-center mb-7 px-6">
      <span class="label-tag">Ce que nous offrons</span>
      <h2 class="section-title mt-1">Nos services</h2>
    </div>
    <div class="grid grid-cols-2 gap-8 px-6 max-w-[720px] mx-auto">

      <!-- Card 1 -->
      <div class="service-card rounded-xl p-4 md:p-4.5 flex flex-col items-center text-center relative" style="background:#f7faf8; border:1px solid #e6efe9; box-shadow:0 10px 24px rgba(15,58,31,0.09), 0 2px 7px rgba(15,58,31,0.08);">
        <span class="service-index absolute top-2 right-2.5 font-display font-bold text-[1.65rem] leading-none select-none" style="color:#1e6b3c;">01</span>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center mt-0.5 mb-2.5" style="background:#ecf7f0;">
          <i class="fas fa-seedling text-lg" style="color:#1e6b3c;"></i>
        </div>
        <div class="w-8 h-0.5 rounded-full mb-2.5" style="background:#cfe2d7;"></div>
        <p class="font-body font-bold text-gray-900 text-[15px] leading-snug mb-0.5">Fraîcheur garantie</p>
        <p class="font-body text-gray-500 text-[13px] leading-snug">Récolte du jour</p>
      </div>

      <!-- Card 2 -->
      <div class="service-card rounded-xl p-4 md:p-4.5 flex flex-col items-center text-center relative" style="background:#f7faf8; border:1px solid #e6efe9; box-shadow:0 10px 24px rgba(15,58,31,0.09), 0 2px 7px rgba(15,58,31,0.08);">
        <span class="service-index absolute top-2 right-2.5 font-display font-bold text-[1.65rem] leading-none select-none" style="color:#1e6b3c;">02</span>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center mt-0.5 mb-2.5" style="background:#ecf7f0;">
          <i class="fas fa-truck-fast text-lg" style="color:#1e6b3c;"></i>
        </div>
        <div class="w-8 h-0.5 rounded-full mb-2.5" style="background:#cfe2d7;"></div>
        <p class="font-body font-bold text-gray-900 text-[15px] leading-snug mb-0.5">Livraison rapide</p>
        <p class="font-body text-gray-500 text-[13px] leading-snug">À votre porte</p>
      </div>

      <!-- Card 3 -->
      <div class="service-card rounded-xl p-4 md:p-4.5 flex flex-col items-center text-center relative" style="background:#f7faf8; border:1px solid #e6efe9; box-shadow:0 10px 24px rgba(15,58,31,0.09), 0 2px 7px rgba(15,58,31,0.08);">
        <span class="service-index absolute top-2 right-2.5 font-display font-bold text-[1.65rem] leading-none select-none" style="color:#1e6b3c;">03</span>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center mt-0.5 mb-2.5" style="background:#ecf7f0;">
          <i class="fas fa-tags text-lg" style="color:#1e6b3c;"></i>
        </div>
        <div class="w-8 h-0.5 rounded-full mb-2.5" style="background:#cfe2d7;"></div>
        <p class="font-body font-bold text-gray-900 text-[15px] leading-snug mb-0.5">Prix équitables</p>
        <p class="font-body text-gray-500 text-[13px] leading-snug">Sans intermédiaires</p>
      </div>

      <!-- Card 4 -->
      <div class="service-card rounded-xl p-4 md:p-4.5 flex flex-col items-center text-center relative" style="background:#f7faf8; border:1px solid #e6efe9; box-shadow:0 10px 24px rgba(15,58,31,0.09), 0 2px 7px rgba(15,58,31,0.08);">
        <span class="service-index absolute top-2 right-2.5 font-display font-bold text-[1.65rem] leading-none select-none" style="color:#1e6b3c;">04</span>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center mt-0.5 mb-2.5" style="background:#ecf7f0;">
          <i class="fas fa-star text-lg" style="color:#1e6b3c;"></i>
        </div>
        <div class="w-8 h-0.5 rounded-full mb-2.5" style="background:#cfe2d7;"></div>
        <p class="font-body font-bold text-gray-900 text-[15px] leading-snug mb-0.5">Qualité premium</p>
        <p class="font-body text-gray-500 text-[13px] leading-snug">Sélection rigoureuse</p>
      </div>

    </div>
  </section>

<!-- ── CATEGORIES ── -->
  <section id="categories-section" class="py-14 px-6 md:px-8" style="background:#faf9f6;">
    <div class="max-w-[980px] mx-auto">
      <div class="text-center mb-8">
        <span class="label-tag">Nos catégories</span>
        <h2 class="section-title mt-1">Découvrez nos produits</h2>
      </div>

      <!-- Category pills (centered) -->
      <div class="flex justify-center mb-8">
        <div class="inline-flex items-center gap-2 p-1.5 rounded-2xl overflow-x-auto scrollbar-hide" style="background:#eef7f1; border:1px solid #deeee4; box-shadow:0 4px 16px rgba(30,107,60,0.09);">
          <button onclick="goShop('all')" class="pill active"><i class="fas fa-th-large text-xs mr-1"></i>Tout</button>
          <button onclick="goShop('vegetables')" class="pill"><i class="fas fa-carrot text-xs mr-1"></i>Légumes</button>
          <button onclick="goShop('fruits')" class="pill"><i class="fas fa-apple-alt text-xs mr-1"></i>Fruits</button>
          <button onclick="goShop('herbs')" class="pill"><i class="fas fa-spa text-xs mr-1"></i>Herbes</button>
        </div>
      </div>

      <!-- Category cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Vegetables card -->
        <div class="sec-block group relative rounded-[20px] overflow-hidden cursor-pointer" onclick="goShop('vegetables')" style="box-shadow:0 14px 34px rgba(30,107,60,0.18), 0 4px 14px rgba(15,58,31,0.12);">
          <div class="relative h-56 md:h-60 overflow-hidden">
            <img src="https://images.unsplash.com/photo-1540420773420-3366772f4999?w=900&q=80" alt="Légumes" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
            <div class="absolute inset-0" style="background:linear-gradient(120deg, rgba(10,38,20,0.92) 0%, rgba(10,38,20,0.55) 52%, rgba(10,38,20,0.18) 100%);"></div>
            <div class="absolute top-4 left-4 px-3 py-1 rounded-full text-[10px] font-body font-bold uppercase tracking-widest" style="background:rgba(240,165,0,0.2); color:#f0c15f; border:1px solid rgba(240,165,0,0.3);">Catégorie</div>
            <div class="absolute inset-0 flex flex-col justify-end p-6">
              <h3 class="text-white text-[1.8rem] font-display font-bold leading-[1.05] tracking-[-0.02em]">Légumes<br>frais</h3>
              <div class="mt-3 inline-flex items-center gap-2 text-white/95 font-body font-semibold text-[13px]">
                Commander <i class="fas fa-arrow-right text-[10px] transition-transform duration-200 group-hover:translate-x-1"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Fruits card -->
        <div class="sec-block group relative rounded-[20px] overflow-hidden cursor-pointer" onclick="goShop('fruits')" style="box-shadow:0 14px 34px rgba(240,165,0,0.16), 0 4px 14px rgba(60,30,5,0.12);">
          <div class="relative h-56 md:h-60 overflow-hidden">
            <img src="https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=900&q=80" alt="Fruits" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
            <div class="absolute inset-0" style="background:linear-gradient(120deg, rgba(60,30,5,0.9) 0%, rgba(60,30,5,0.52) 52%, rgba(60,30,5,0.16) 100%);"></div>
            <div class="absolute top-4 left-4 px-3 py-1 rounded-full text-[10px] font-body font-bold uppercase tracking-widest" style="background:rgba(240,165,0,0.2); color:#f0c15f; border:1px solid rgba(240,165,0,0.3);">Catégorie</div>
            <div class="absolute inset-0 flex flex-col justify-end p-6">
              <h3 class="text-white text-[1.8rem] font-display font-bold leading-[1.05] tracking-[-0.02em]">Fruits<br>naturels</h3>
              <div class="mt-3 inline-flex items-center gap-2 text-white/95 font-body font-semibold text-[13px]">
                Commander <i class="fas fa-arrow-right text-[10px] transition-transform duration-200 group-hover:translate-x-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

 <!-- ── DARK CTA BAND ── -->
  <section class="py-16 px-8 text-center" style="background:linear-gradient(160deg, #eaf5ee 0%, #f4fbf7 55%, #ffffff 100%); border-top:1px solid #dfede4; border-bottom:1px solid #dfede4;">
    <div class="max-w-xs mx-auto">
      <span class="inline-block mb-2 px-3 py-1 rounded-full font-body font-semibold text-[11px] uppercase tracking-[0.16em]" style="color:#1e6b3c; background:#e3f3ea;">Commandez maintenant</span>
      <h2 class="text-3xl font-display font-bold leading-tight mb-4" style="color:#123b24;">
        Livraison <em style="color:#f0a500;">à domicile</em>
      </h2>
      <p class="font-body text-sm leading-[1.75] mb-8" style="color:#4c6b58;">
        Passez votre commande en quelques clics et recevez vos produits frais directement chez vous.
      </p>
      <button onclick="showPage('shop')" class="cta-btn inline-flex items-center gap-2.5 px-8 py-4 rounded-xl font-body font-semibold text-sm" style="background: linear-gradient(135deg, #f0a500, #d99300); color:#0f2a0f; box-shadow: 0 8px 28px rgba(240,165,0,0.4);">
        <i class="fas fa-shopping-basket"></i> Accéder à la boutique
      </button>
    </div>
  </section>

  <!-- ── FOOTER + CONTACT ── -->
  <footer id="footer-section" class="footer-wrap min-h-screen px-6 md:px-8 py-12 md:py-14 flex items-center">
    <div class="max-w-[1120px] mx-auto w-full">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 md:gap-8 footer-main-grid">
        <div class="footer-col">
          <div class="flex items-center gap-2.5 mb-4">
            <div class="w-10 h-10 rounded-full overflow-hidden border" style="border-color:#d8e5dd;">
              <img src="images/logo 1.jpeg" alt="SoukFreshy" class="w-full h-full object-cover">
            </div>
            <span class="font-display font-bold text-[#101828] text-[1.4rem]">Souk<span style="color:#1e6b3c;">Freshy</span></span>
          </div>
          <p class="font-body text-[#5f6b64] text-[17px] leading-[1.65] max-w-[28ch] mb-5">
            Fraicheur locale, livraison rapide et qualite de confiance pour votre quotidien.
          </p>
          <div class="flex items-center gap-3.5">
            <a href="https://x.com" target="_blank" class="footer-social-icon"><i class="fab fa-x-twitter"></i></a>
            <a href="https://linkedin.com" target="_blank" class="footer-social-icon"><i class="fab fa-linkedin-in"></i></a>
          </div>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">Navigation</h4>
          <div class="space-y-3.5">
            <button onclick="showPage('home')" class="footer-link-btn">Accueil</button>
            <button onclick="document.getElementById('welcome-section')?.scrollIntoView({behavior:'smooth'});" class="footer-link-btn">A propos</button>
            <button onclick="document.getElementById('features-strip')?.scrollIntoView({behavior:'smooth'});" class="footer-link-btn">Nos services</button>
            <button onclick="showPage('shop')" class="footer-link-btn">Produits</button>
          </div>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">Contact us</h4>
          <div class="space-y-3.5 text-[16px] text-[#5f6b64] font-body">
            <p class="inline-flex items-center gap-2"><i class="fas fa-phone text-[13px] text-[#1e6b3c]"></i> +213 770 00 00 00</p>
            <p class="inline-flex items-center gap-2"><i class="fas fa-envelope text-[13px] text-[#1e6b3c]"></i> contact@soukfreshy.dz</p>
            <p class="inline-flex items-center gap-2"><i class="fas fa-location-dot text-[13px] text-[#1e6b3c]"></i> Sidi bel abbes, Algerie</p>
            <a href="https://facebook.com" target="_blank" class="footer-social-btn fb mt-1 inline-flex">
              <i class="fab fa-facebook text-lg"></i><span>Facebook</span>
            </a>
            <a href="https://wa.me/213770000000" target="_blank" class="footer-social-btn wa inline-flex">
              <i class="fab fa-whatsapp text-lg"></i><span>WhatsApp</span>
            </a>
          </div>
        </div>

        <div class="footer-col">
          <h4 class="footer-heading">Support</h4>
          <div class="space-y-3.5">
            <a href="#" class="footer-link-btn">FAQ</a>
            <a href="#" class="footer-link-btn">Livraison</a>
            <a href="#" class="footer-link-btn">Politique de retour</a>
            <a href="#" class="footer-link-btn">Confidentialite</a>
          </div>
        </div>
      </div>

      <div class="footer-divider my-9"></div>

      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 footer-col">
        <p class="font-body text-[14px] text-[#7a8780]">© 2024 SoukFreshy. Tous droits reserves.</p>
        <div class="flex items-center gap-5">
          <a href="#" class="footer-link-btn text-[14px]">Terms and Conditions</a>
          <a href="#" class="footer-link-btn text-[14px]">Privacy Policy</a>
        </div>
      </div>
    </div>
  </footer>
</div>

<!-- ══════════════════════════════════════════════════════════
     SHOP PAGE
══════════════════════════════════════════════════════════ -->
<div id="page-shop" class="page hidden min-h-screen pb-8">

  <!-- TOP NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 z-50" style="background:rgba(15,58,31,0.96); backdrop-filter: blur(8px); border-bottom:1px solid rgba(187,234,209,0.14); box-shadow:0 8px 24px rgba(0,0,0,0.18);">
    <div class="flex items-center justify-between px-6 md:px-8 py-3">
      <button onclick="showPage('home')" class="flex items-center gap-2.5 focus:outline-none text-white">
        <i class="fas fa-arrow-left text-sm"></i>
        <div class="w-8 h-8 rounded-full overflow-hidden border-2" style="border-color:rgba(240,165,0,0.5);">
          <img src="images/logo 1.jpeg" alt="" class="w-full h-full object-cover">
        </div>
        <span class="font-display font-bold text-[1rem] text-white">Boutique</span>
      </button>
      <div class="flex items-center gap-1.5">
        <button class="nav-btn-dark" onclick="toggleSearch('shop-search')" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14); border-radius:12px;"><i class="fas fa-search"></i></button>
        <button class="nav-btn-dark relative" onclick="showPage('wishlist')" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14); border-radius:12px;">
          <i class="fas fa-heart"></i>
          <span id="badge-wishlist-shop" class="cart-badge hidden" style="background:#ef4444;">0</span>
        </button>
        <button class="nav-btn-dark relative" onclick="showPage('panier')" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14); border-radius:12px;">
          <i class="fas fa-shopping-cart"></i>
          <span id="badge-shop" class="cart-badge hidden">0</span>
        </button>
      </div>
    </div>
    <div id="shop-search" class="search-bar hidden px-6 md:px-8 pb-3.5">
      <div class="flex items-center gap-2.5 rounded-xl px-4 py-2.5" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.15);">
        <i class="fas fa-search text-white/60 text-sm"></i>
        <input type="text" placeholder="Rechercher des produits..." class="bg-transparent flex-1 text-sm font-body outline-none text-white placeholder-white/50" oninput="searchProducts(this.value)">
      </div>
    </div>
  </nav>

  <div class="pt-[78px] px-6 md:px-8" style="background:linear-gradient(180deg, #f2f8f5 0%, #ffffff 220px);">
    <div class="py-4 flex justify-center">
      <div class="inline-flex items-center gap-2 overflow-x-auto scrollbar-hide p-1.5 rounded-2xl" style="background:#edf6f1; border:1px solid #deece4; box-shadow:0 6px 18px rgba(30,107,60,0.08);">
        <button onclick="filterCat('all')" class="filter-btn active"><i class="fas fa-th-large text-xs mr-1"></i> Tout</button>
        <button onclick="filterCat('vegetables')" class="filter-btn"><i class="fas fa-carrot text-xs mr-1"></i> Légumes</button>
        <button onclick="filterCat('fruits')" class="filter-btn"><i class="fas fa-apple-alt text-xs mr-1"></i> Fruits</button>
        <button onclick="filterCat('herbs')" class="filter-btn"><i class="fas fa-spa text-xs mr-1"></i> Herbes</button>
      </div>
    </div>
    <div class="flex items-center justify-between mb-5 mt-1">
      <h2 id="shop-title" class="text-xl font-display font-bold text-gray-900">Tous les produits</h2>
      <span id="prod-count" class="text-gray-500 text-sm font-body">12 produits</span>
    </div>
    <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-4 max-w-[1200px] mx-auto"></div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PRODUCT DETAIL PAGE
══════════════════════════════════════════════════════════ -->
<div id="page-product" class="page hidden min-h-screen pb-28">

  <!-- TOP NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 z-50" style="background:rgba(15,58,31,0.96); backdrop-filter:blur(8px); border-bottom:1px solid rgba(187,234,209,0.14); box-shadow:0 8px 24px rgba(0,0,0,0.18);">
    <div class="flex items-center justify-between px-6 py-3">
      <button onclick="showPage('shop')" class="flex items-center gap-2 font-body font-semibold text-sm text-white focus:outline-none">
        <i class="fas fa-arrow-left text-sm"></i> Boutique
      </button>
      <div class="flex items-center gap-1.5">
        <button id="wish-nav-btn" class="nav-btn-dark" onclick="toggleWishCurrent()" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14); border-radius:12px;">
          <i class="fas fa-heart"></i>
        </button>
        <button class="nav-btn-dark relative" onclick="showPage('panier')" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14); border-radius:12px;">
          <i class="fas fa-shopping-cart"></i>
          <span id="badge-product" class="cart-badge hidden">0</span>
        </button>
      </div>
    </div>
  </nav>

  <div id="product-content" class="pt-[72px] pb-4"></div>

  <!-- Fixed bottom cart bar -->
  <div class="fixed bottom-0 left-0 right-0 z-40 px-5 md:px-8 pb-3 pt-2.5" style="background:linear-gradient(to top, rgba(255,255,255,0.98) 72%, rgba(255,255,255,0.82) 100%); backdrop-filter: blur(8px); border-top:1px solid #e7f1eb; box-shadow:0 -8px 30px rgba(0,0,0,0.1);">
    <div class="max-w-[920px] mx-auto flex items-center gap-3">
      <div class="flex items-center gap-2 rounded-xl px-3 py-2" style="background:#eef5f0; border:1px solid #dcebe2;">
        <button onclick="decQty()" class="w-8 h-8 bg-white rounded-lg flex items-center justify-center font-bold shadow-sm transition-transform duration-150 active:scale-90" style="color:#1e6b3c;">−</button>
        <span id="qty-display" class="w-7 text-center font-body font-bold text-gray-800 text-sm">1</span>
        <span id="qty-unit" class="font-body text-xs font-semibold text-gray-400">kg</span>
        <button onclick="incQty()" class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white shadow-sm transition-transform duration-150 active:scale-90" style="background:#1e6b3c;">+</button>
      </div>
      <button onclick="doAddToCart()" class="flex-1 text-white font-body font-semibold py-3 rounded-xl flex items-center justify-center gap-2 active:scale-95 transition-transform duration-150" style="background: linear-gradient(135deg, #1e6b3c, #27a163); box-shadow: 0 6px 20px rgba(30,107,60,0.3);">
        <i class="fas fa-shopping-cart text-sm"></i> Ajouter au panier
      </button>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     WISHLIST PAGE
══════════════════════════════════════════════════════════ -->
<div id="page-wishlist" class="page hidden min-h-screen pb-8">

  <!-- TOP NAVBAR -->
  <nav class="fixed top-0 left-0 right-0 z-50" style="background:rgba(15,58,31,0.96); backdrop-filter:blur(8px); border-bottom:1px solid rgba(187,234,209,0.14); box-shadow:0 8px 24px rgba(0,0,0,0.18);">
    <div class="flex items-center justify-between px-6 py-3">
      <button onclick="showPage('shop')" class="flex items-center gap-2 font-body font-semibold text-sm text-white focus:outline-none">
        <i class="fas fa-arrow-left text-sm"></i> Boutique
      </button>
      <span class="font-display font-bold text-white text-[1rem]">
        <i class="fas fa-heart mr-1.5" style="color:#ef4444; font-size:13px;"></i>Favoris
        <span id="wishlist-header-count" class="font-body text-white/50 text-sm font-normal ml-1"></span>
      </span>
      <button onclick="showPage('panier')" class="nav-btn-dark relative" style="background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.14); border-radius:12px;">
        <i class="fas fa-shopping-cart"></i>
        <span id="badge-wishlist-page" class="cart-badge hidden">0</span>
      </button>
    </div>
  </nav>

  <div class="pt-[72px] px-5 max-w-lg mx-auto w-full">
    <div id="wishlist-container" class="py-4"></div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PANIER PAGE
══════════════════════════════════════════════════════════ -->
<div id="page-panier" class="page hidden min-h-screen" style="background:linear-gradient(180deg,#f3faf6 0%,#ffffff 42%,#ffffff 100%);">

  <!-- TOP NAVBAR -->
  <nav class="home-nav fixed top-0 left-0 right-0 z-50">
    <div class="flex items-center justify-between px-5 md:px-6 py-2.5">
      <button onclick="showPage('shop')" class="nav-link-main inline-flex items-center gap-2.5 text-white/85 hover:text-white transition-colors duration-150 font-body font-semibold text-sm focus:outline-none">
        <i class="fas fa-arrow-left text-sm"></i> Boutique
      </button>
      <span class="font-display font-bold text-white text-[1.02rem] tracking-tight">
        <i class="fas fa-shopping-cart mr-1.5" style="font-size:13px; color:#f0a500;"></i>Mon Panier
        <span id="panier-header-count" class="font-body text-white/50 text-sm font-normal ml-1"></span>
      </span>
      <button onclick="showPage('wishlist')" class="nav-btn-dark relative" style="background:rgba(240,165,0,0.13); border:1px solid rgba(240,165,0,0.26); border-radius:12px; color:#ffe3a8;">
        <i class="fas fa-heart"></i>
        <span id="badge-panier-wish" class="cart-badge hidden" style="background:#ef4444;">0</span>
      </button>
    </div>
  </nav>

  <div class="pt-[84px] px-5 max-w-lg mx-auto w-full pb-8">
    <div class="pr-1">
      <div class="mb-4 rounded-2xl px-4 py-3 flex items-center justify-between" style="background:rgba(255,255,255,0.78); border:1px solid #e1efe7; box-shadow:0 8px 20px rgba(15,58,31,0.06); backdrop-filter:blur(6px);">
        <div>
          <p class="font-display font-bold text-gray-900 text-[1.02rem] leading-tight">Votre sélection</p>
          <p class="font-body text-[12px] text-gray-500">Produits frais, prix transparents</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-body font-semibold" style="background:#ecf8f1; color:#1e6b3c; border:1px solid #d4ebde;">
          <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background:#27a163;"></span>
          Livraison rapide
        </span>
      </div>
      <div id="panier-container" class="py-1 space-y-3"></div>
    </div>
    <div id="panier-footer" class="mt-4 pb-6">
      <div class="rounded-2xl p-4" style="background:#ffffff; border:1px solid #e3f1e8; box-shadow:0 10px 26px rgba(15,58,31,0.08);">
      <div class="flex items-center justify-between mb-1.5">
        <span class="font-body text-gray-500 text-sm">Sous-total</span>
        <span id="panier-subtotal" class="font-body font-bold text-gray-900 text-[0.95rem]">0 DA</span>
      </div>
      <div class="flex items-center justify-between mb-3.5">
        <span class="font-body text-gray-500 text-sm">Livraison</span>
        <span id="panier-delivery-display" class="font-body text-sm font-semibold px-2 py-0.5 rounded-full" style="background:#edf9f2; color:#27a163;">Gratuite</span>
      </div>
      <div class="flex items-center justify-between py-3.5 mb-4 rounded-xl px-3" style="background:#f7fcf9; border:1px dashed #d7e9df;">
        <span class="font-display font-bold text-gray-900 text-base">Total</span>
        <span id="panier-total" class="font-display font-bold text-[1.3rem]" style="color:#1e6b3c;">0 DA</span>
      </div>
      <button id="checkout-btn" onclick="doCheckout()" class="w-full py-3.5 rounded-xl font-body font-semibold text-sm text-white flex items-center justify-center gap-2 transition-all duration-200 hover:-translate-y-[1px] hover:shadow-xl active:scale-[0.98]" style="background:linear-gradient(135deg,#1e6b3c,#27a163); box-shadow:0 8px 24px rgba(30,107,60,0.34);">
        <i class="fas fa-lock text-xs"></i>
        <span>Passer la commande</span>
        <i class="fas fa-arrow-right text-[11px] opacity-90"></i>
      </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     CHECKOUT PAGE
══════════════════════════════════════════════════════════ -->
<div id="page-checkout" class="page hidden min-h-screen" style="background:linear-gradient(180deg,#f3faf6 0%,#ffffff 42%,#ffffff 100%);">

  <!-- TOP NAVBAR -->
  <nav class="home-nav fixed top-0 left-0 right-0 z-50">
    <div class="flex items-center justify-between px-5 md:px-6 py-2.5">
      <button onclick="showPage('panier')" class="nav-link-main inline-flex items-center gap-2.5 text-white/85 hover:text-white transition-colors duration-150 font-body font-semibold text-sm focus:outline-none">
        <i class="fas fa-arrow-left text-sm"></i> Panier
      </button>
      <span class="font-display font-bold text-white text-[1.02rem] tracking-tight">
        <i class="fas fa-clipboard-check mr-1.5" style="font-size:13px; color:#f0a500;"></i>Confirmation
      </span>
      <div class="w-10"></div>
    </div>
  </nav>

  <div class="pt-[84px] px-5 max-w-lg mx-auto w-full pb-8">
    <div id="checkout-body">

      <!-- Order items summary -->
      <div class="mb-4 rounded-2xl overflow-hidden" style="border:1px solid #e3f1e8; box-shadow:0 4px 16px rgba(15,58,31,0.06);">
        <div class="flex items-center justify-between px-4 py-3" style="background:#f7fcf9; border-bottom:1px solid #e3f1e8;">
          <span class="font-display font-bold text-gray-900 text-[0.95rem]">Récapitulatif</span>
          <span id="checkout-item-count" class="font-body text-xs text-gray-400"></span>
        </div>
        <div id="checkout-items-list" class="px-4 py-3 space-y-1 bg-white"></div>
      </div>

      <!-- Delivery info form -->
      <div class="rounded-2xl p-4 mb-4 bg-white" style="border:1px solid #e3f1e8; box-shadow:0 4px 16px rgba(15,58,31,0.06);">
        <div class="flex items-center gap-2 mb-3.5">
          <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#ecf8f1;">
            <i class="fas fa-map-marker-alt text-sm" style="color:#1e6b3c;"></i>
          </div>
          <span class="font-display font-bold text-gray-900 text-[0.95rem]">Informations de livraison</span>
        </div>
        <div class="space-y-2.5">
          <input id="co-name"    type="text" placeholder="Nom complet *"  class="w-full rounded-xl px-4 py-2.5 font-body text-sm text-gray-800 outline-none" style="background:#f7fcf9; border:1.5px solid #dceee3;">
          <input id="co-phone"   type="tel"  placeholder="Téléphone *"    class="w-full rounded-xl px-4 py-2.5 font-body text-sm text-gray-800 outline-none" style="background:#f7fcf9; border:1.5px solid #dceee3;">
          <div class="grid grid-cols-2 gap-2">
            <input id="co-wilaya"  type="text" placeholder="Wilaya *"   class="w-full rounded-xl px-4 py-2.5 font-body text-sm text-gray-800 outline-none" style="background:#f7fcf9; border:1.5px solid #dceee3;">
            <input id="co-commune" type="text" placeholder="Commune"    class="w-full rounded-xl px-4 py-2.5 font-body text-sm text-gray-800 outline-none" style="background:#f7fcf9; border:1.5px solid #dceee3;">
          </div>
          <textarea id="co-address" placeholder="Adresse précise (optionnel)" rows="2" class="w-full rounded-xl px-4 py-2.5 font-body text-sm text-gray-800 outline-none resize-none" style="background:#f7fcf9; border:1.5px solid #dceee3;"></textarea>
        </div>
      </div>

      <!-- Payment method: cash on delivery -->
      <div class="rounded-2xl p-4 mb-4 bg-white" style="border:1px solid #e3f1e8; box-shadow:0 4px 16px rgba(15,58,31,0.06);">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#ecf8f1,#d4ebde);">
            <i class="fas fa-money-bill-wave" style="color:#1e6b3c;"></i>
          </div>
          <div class="flex-1">
            <p class="font-display font-bold text-gray-900 text-[0.9rem]">Paiement à la livraison</p>
            <p class="font-body text-xs text-gray-400 mt-0.5">Payez en espèces lors de la réception de votre commande</p>
          </div>
          <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0" style="background:#1e6b3c;">
            <i class="fas fa-check text-white" style="font-size:10px;"></i>
          </div>
        </div>
      </div>

      <!-- Order total -->
      <div class="rounded-2xl p-4 mb-4 bg-white" style="border:1px solid #e3f1e8; box-shadow:0 4px 16px rgba(15,58,31,0.06);">
        <div class="flex items-center justify-between mb-1.5">
          <span class="font-body text-gray-500 text-sm">Sous-total</span>
          <span id="co-subtotal" class="font-body font-bold text-gray-900 text-[0.95rem]">0 DA</span>
        </div>
        <div class="flex items-center justify-between mb-3.5">
          <span class="font-body text-gray-500 text-sm">Livraison</span>
          <span id="co-delivery" class="font-body text-sm font-semibold px-2 py-0.5 rounded-full" style="background:#edf9f2; color:#27a163;">Gratuite</span>
        </div>
        <div class="flex items-center justify-between py-3.5 rounded-xl px-3" style="background:#f7fcf9; border:1px dashed #d7e9df;">
          <span class="font-display font-bold text-gray-900 text-base">Total à payer</span>
          <span id="co-total" class="font-display font-bold text-[1.3rem]" style="color:#1e6b3c;">0 DA</span>
        </div>
      </div>

      <!-- Confirm button -->
      <button id="confirm-order-btn" onclick="confirmOrder()" class="w-full py-3.5 rounded-xl font-body font-semibold text-sm text-white flex items-center justify-center gap-2 transition-all duration-200 hover:-translate-y-[1px] hover:shadow-xl active:scale-[0.98]" style="background:linear-gradient(135deg,#1e6b3c,#27a163); box-shadow:0 8px 24px rgba(30,107,60,0.34);">
        <i class="fas fa-check-circle text-xs"></i>
        <span>Confirmer la commande</span>
        <i class="fas fa-arrow-right text-[11px] opacity-90"></i>
      </button>

    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     LOGIN MODAL
══════════════════════════════════════════════════════════ -->
<div id="login-modal" class="fixed inset-0 z-[200] hidden items-center justify-center px-4 py-4 sm:py-6">
  <div class="absolute inset-0 bg-black/60 auth-backdrop" onclick="closeModal()"></div>
  <div id="modal-box" class="relative my-auto bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl auth-modal-shell">
    <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors duration-150 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
      <i class="fas fa-times"></i>
    </button>
    <div class="text-center mb-5">
      <div class="w-16 h-16 rounded-full overflow-hidden border-2 border-gray-100 mx-auto mb-3">
        <img src="images/logo 1.jpeg" alt="" class="w-full h-full object-cover">
      </div>
      <h2 id="auth-modal-title" class="text-xl font-display font-bold text-gray-900">Connexion</h2>
      <p id="auth-modal-subtitle" class="text-gray-500 text-xs font-body mt-1">Connectez-vous pour continuer</p>
    </div>

    <div class="auth-mode-switch mb-4">
      <button id="mode-login-btn" class="auth-mode-btn active" onclick="switchAuthMode('login')">Connexion</button>
      <button id="mode-register-btn" class="auth-mode-btn" onclick="switchAuthMode('register')">Créer un compte</button>
    </div>

    <form id="auth-login-form" class="space-y-3">
      <input id="login-identifier" type="text" placeholder="Numéro de téléphone ou email" class="input-field" required>
      <input id="login-password" type="password" placeholder="Mot de passe" class="input-field" required>
      <div class="auth-role-row">
        <span class="auth-role-label">Rôle</span>
        <div class="auth-role-options">
          <label class="auth-role-chip">
            <input type="radio" name="login-role" value="consumer" checked>
            <span>Consumer</span>
          </label>
          <label class="auth-role-chip">
            <input type="radio" name="login-role" value="farmer">
            <span>Farmer</span>
          </label>
        </div>
      </div>
      <button type="submit" class="cta-btn w-full py-3 rounded-xl font-body font-semibold text-white" style="background: linear-gradient(135deg,#1e6b3c,#27a163);">Se connecter</button>
    </form>

    <form id="auth-register-form" class="space-y-3 hidden">
      <input id="register-full-name" type="text" placeholder="Nom complet" class="input-field" required>
      <input id="register-phone" type="tel" placeholder="Numéro de téléphone" class="input-field" required>
      <input id="register-email" type="email" placeholder="Email (optionnel)" class="input-field">
      <input id="register-password" type="password" placeholder="Mot de passe" class="input-field" required>
      <div class="auth-role-row">
        <span class="auth-role-label">Rôle</span>
        <div class="auth-role-options">
          <label class="auth-role-chip">
            <input type="radio" name="register-role" value="consumer" checked onchange="toggleRegisterFields()">
            <span>Consumer</span>
          </label>
          <label class="auth-role-chip">
            <input type="radio" name="register-role" value="farmer" onchange="toggleRegisterFields()">
            <span>Farmer</span>
          </label>
        </div>
      </div>
      <div id="farmer-extra-fields" class="hidden space-y-2.5">
        <input id="register-wilaya" type="text" placeholder="Wilaya (obligatoire pour les agriculteurs)" class="input-field">
        <input id="register-commune" type="text" placeholder="Commune" class="input-field">
      </div>
      <button type="submit" class="cta-btn w-full py-3 rounded-xl font-body font-semibold text-white" style="background: linear-gradient(135deg,#1e6b3c,#27a163);">Créer le compte</button>
    </form>

    <p id="auth-message" class="auth-inline-message hidden"></p>

    <div class="mt-4 text-center">
      <p class="text-[11px] text-gray-400 font-body">Vous pouvez parcourir les produits sans compte. Le compte est requis pour commander.</p>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     PROFILE MODAL
══════════════════════════════════════════════════════════ -->
<div id="profile-modal" class="fixed inset-0 z-[205] hidden items-center justify-center">
  <div class="absolute inset-0 bg-black/60 auth-backdrop" onclick="closeProfileModal()"></div>
  <div class="relative bg-white rounded-2xl mx-4 w-full max-w-md shadow-2xl auth-modal-shell">

    <!-- Header -->
    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
      <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#1e6b3c,#27a163);">
        <i class="fas fa-user text-white text-base"></i>
      </div>
      <div class="flex-1 min-w-0">
        <p id="profile-name" class="font-display font-bold text-gray-900 text-base truncate">-</p>
        <span id="profile-role-badge" class="inline-block text-[11px] font-body font-semibold px-2 py-0.5 rounded-full mt-0.5" style="background:#e8f5ee; color:#1e6b3c;">Consumer</span>
      </div>
      <button onclick="closeProfileModal()" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 flex-shrink-0">
        <i class="fas fa-times text-sm"></i>
      </button>
    </div>

    <!-- Info cards -->
    <div class="px-5 pt-4 pb-3 grid grid-cols-2 gap-2">
      <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
        <p class="text-[10px] text-gray-400 font-body uppercase tracking-wide">Téléphone</p>
        <p id="profile-phone" class="font-body text-sm font-semibold text-gray-800 truncate">-</p>
      </div>
      <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2">
        <p class="text-[10px] text-gray-400 font-body uppercase tracking-wide">Wilaya</p>
        <p id="profile-wilaya" class="font-body text-sm font-semibold text-gray-800 truncate">-</p>
      </div>
    </div>

    <!-- Orders section -->
    <div class="px-5 pb-3">
      <p class="text-[11px] font-body font-semibold text-gray-400 uppercase tracking-wide mb-2.5">
        <i class="fas fa-box mr-1"></i>Mes commandes
      </p>
      <div id="profile-orders" class="space-y-2" style="max-height:280px; overflow-y:auto; padding-right:2px;">
        <p class="text-sm text-gray-400 font-body text-center py-8">
          <i class="fas fa-spinner fa-spin block text-xl mb-2" style="color:#27a163;"></i>Chargement…
        </p>
      </div>
    </div>

    <!-- Logout -->
    <div class="px-5 py-4 border-t border-gray-100">
      <button onclick="logoutAuthUser()" class="w-full py-2.5 rounded-xl font-body font-semibold text-white text-sm flex items-center justify-center gap-2" style="background:linear-gradient(135deg,#b91c1c,#ef4444);">
        <i class="fas fa-arrow-right-from-bracket"></i>Se déconnecter
      </button>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     TOAST
══════════════════════════════════════════════════════════ -->
<div id="toast" class="fixed top-20 left-1/2 -translate-x-1/2 z-[300] hidden">
  <div class="flex items-center gap-2 text-white text-sm font-body px-4 py-2.5 rounded-xl shadow-xl" style="background: #1e6b3c;">
    <i class="fas fa-check-circle" style="color:#87d8b0;"></i>
    <span id="toast-msg">Ajouté au panier !</span>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     VIDEO MODAL
══════════════════════════════════════════════════════════ -->
<div id="video-modal" class="video-modal-backdrop hidden" onclick="closeVideoModal(event)">
  <div class="video-modal-container" role="dialog" aria-modal="true" aria-label="Vidéo SoukFreshy">
    <button onclick="closeVideoModal()" class="video-modal-close" aria-label="Fermer">
      <i class="fas fa-times"></i>
    </button>
    <div class="video-wrapper">
      <video id="hero-video" class="w-full h-full object-cover rounded-2xl" controls playsinline preload="none" poster="">
        <source src="videos/soukfrechy.mp4" type="video/mp4">
      </video>
    </div>
    <p class="video-caption">
      <i class="fas fa-leaf" style="color:#f0a500;"></i>
      Fraîcheur directe des fermes algériennes à votre porte
    </p>
  </div>
</div>

<script>
window.__SF_OPEN_LOGIN = <?php echo json_encode(in_array($_GET['login'] ?? '', ['consumer','farmer'], true) ? $_GET['login'] : null); ?>;
window.__SF_SESSION = <?php
    echo json_encode(
        empty($_SESSION['user_id'])
            ? ['logged_in' => false]
            : ['logged_in' => true, 'user' => [
                'id'        => $_SESSION['user_id'],
                'full_name' => $_SESSION['full_name'],
                'role'      => $_SESSION['role'],
                'phone'     => $_SESSION['phone'] ?? '',
                'email'     => $_SESSION['email'] ?? '',
                'wilaya'    => $_SESSION['wilaya'] ?? '',
                'commune'   => $_SESSION['commune'] ?? '',
              ]]
    );
?>;
</script>
<script src="user/script.js"></script>
<script src="user/auth.js"></script>
<script>
  function openVideoModal() {
    const modal = document.getElementById('video-modal');
    const video = document.getElementById('hero-video');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('open'));
    video.play();
    document.body.style.overflow = 'hidden';
  }
  function closeVideoModal(e) {
    if (e && e.target !== e.currentTarget) return;
    const modal = document.getElementById('video-modal');
    const video = document.getElementById('hero-video');
    modal.classList.remove('open');
    video.pause();
    video.currentTime = 0;
    document.body.style.overflow = '';
    setTimeout(() => modal.classList.add('hidden'), 320);
  }
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeVideoModal();
  });
  function toggleWelcomeVideo() {
    const video = document.getElementById('welcome-video');
    const icon  = document.getElementById('welcome-video-icon');
    if (video.paused) {
      video.play();
      icon.className = 'fas fa-pause text-[11px]';
    } else {
      video.pause();
      icon.className = 'fas fa-play text-[11px]';
    }
  }
</script>
</body>
</html>
