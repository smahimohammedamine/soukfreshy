/* ═══════════════════════════════════════════════
   SOUKFRESHY — Arabic / French translation (i18n)
   Adds a floating language toggle to the consumer
   site and the farmer dashboard. The brand name
   "SoukFreshy" is never translated.
   FR  ⇄  AR (RTL)
═══════════════════════════════════════════════ */
(function () {
  'use strict';

  const STORAGE_KEY = 'sf_lang';
  const SKIP_TAGS = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEXTAREA', 'CODE']);

  // ── FR → AR dictionary (exact whole-string match, trimmed) ──
  const I18N = {
    // Navigation / général
    'À propos': 'من نحن',
    'A propos': 'من نحن',
    'Nos services': 'خدماتنا',
    'Catégories': 'الفئات',
    'Produits': 'المنتجات',
    'Connexion': 'تسجيل الدخول',
    'Profil': 'الملف الشخصي',
    'Bienvenue': 'مرحباً',

    // Hero
    'Fraîcheur garantie': 'نضارة مضمونة',
    'Fruits & légumes': 'فواكه وخضروات',
    'frais chaque jour': 'طازجة كل يوم',
    'Directement des fermes algériennes à votre porte — sans intermédiaires.': 'مباشرة من المزارع الجزائرية إلى باب منزلك — دون وسطاء.',
    'Commander maintenant': 'اطلب الآن',
    'La fraîcheur des fermes algériennes, livrée avec confiance à votre table.': 'نضارة المزارع الجزائرية، تُوصَّل بثقة إلى مائدتك.',

    // Bienvenue / mission
    'Bienvenue sur nos plateformes': 'مرحباً بكم في منصتنا',
    'La première plateforme algérienne qui connecte directement les agriculteurs aux consommateurs pour une expérience plus simple et plus transparente.': 'أول منصة جزائرية تربط المزارعين مباشرة بالمستهلكين لتجربة أبسط وأكثر شفافية.',
    'Notre mission est de livrer des produits frais, des prix justes et une qualité locale sans intermédiaires.': 'مهمتنا هي توصيل منتجات طازجة بأسعار عادلة وجودة محلية دون وسطاء.',
    'Clients': 'عملاء',
    'satisfaits': 'راضون',
    'Wilayate': 'ولايات',
    'servies': 'مخدومة',
    'locaux': 'محلية',

    // Services
    'Ce que nous offrons': 'ما نقدمه',
    'Récolte du jour': 'حصاد اليوم',
    'Livraison rapide': 'توصيل سريع',
    'À votre porte': 'إلى باب منزلك',
    'Prix équitables': 'أسعار عادلة',
    'Sans intermédiaires': 'دون وسطاء',
    'Qualité premium': 'جودة ممتازة',
    'Sélection rigoureuse': 'انتقاء دقيق',

    // Catégories
    'Nos catégories': 'فئاتنا',
    'Découvrez nos produits': 'اكتشف منتجاتنا',
    'Tout': 'الكل',
    'Légumes': 'خضروات',
    'Fruits': 'فواكه',
    'Herbes': 'أعشاب',
    'Catégorie': 'فئة',
    'frais': 'طازجة',
    'Commander': 'اطلب',
    'naturels': 'طبيعية',
    'Offre spéciale': 'عرض خاص',
    'Packs': 'باقات',
    'fraîcheur': 'نضارة',
    'Découvrir': 'اكتشف',

    // CTA
    'Commandez maintenant': 'اطلب الآن',
    'Livraison': 'التوصيل',
    'à domicile': 'إلى المنزل',
    'Passez votre commande en quelques clics et recevez vos produits frais directement chez vous.': 'قدّم طلبك ببضع نقرات واستلم منتجاتك الطازجة مباشرة في منزلك.',
    'Accéder à la boutique': 'الدخول إلى المتجر',

    // Boîtes
    'Nos Boîtes': 'صناديقنا',
    'Boîtes': 'صناديق',
    'Fraîcheur': 'النضارة',
    'Boîtes de la semaine et éditions saisonnières — fraîcheur garantie, prix réduits.': 'صناديق الأسبوع وإصدارات موسمية — نضارة مضمونة وأسعار مخفّضة.',
    'De la semaine': 'الأسبوعية',
    'Saisonnières': 'الموسمية',
    'Aucune boîte disponible pour le moment.': 'لا توجد صناديق متاحة حالياً.',
    'Toutes': 'الكل',
    'Printemps': 'الربيع',
    'Été': 'الصيف',
    'Automne': 'الخريف',
    'Hiver': 'الشتاء',
    'Aucune boîte saisonnière disponible actuellement.': 'لا توجد صناديق موسمية متاحة حالياً.',

    // Footer
    'Fraicheur locale, livraison rapide et qualite de confiance pour votre quotidien.': 'نضارة محلية وتوصيل سريع وجودة موثوقة ليومياتك.',
    'Navigation': 'التنقل',
    'Accueil': 'الرئيسية',
    'Contact us': 'اتصل بنا',
    'Support': 'الدعم',
    'FAQ': 'الأسئلة الشائعة',
    'Politique de retour': 'سياسة الإرجاع',
    'Confidentialite': 'الخصوصية',
    '© 2024 SoukFreshy. Tous droits reserves.': '© 2024 SoukFreshy. جميع الحقوق محفوظة.',
    'Terms and Conditions': 'الشروط والأحكام',
    'Privacy Policy': 'سياسة الخصوصية',

    // Détail boîte
    'Produits inclus': 'المنتجات المضمَّنة',
    'Prix': 'السعر',
    'Disponibilité': 'التوفر',
    'Livraison gratuite': 'توصيل مجاني',
    'Ajouter au panier': 'إضافة إلى السلة',
    'Mixte': 'مشكَّل',
    'Dernier jour !': 'اليوم الأخير !',
    'Aucun produit listé.': 'لا توجد منتجات مدرجة.',
    'Voir le panier →': 'عرض السلة',

    // Boutique
    'Boutique': 'المتجر',
    'Rechercher des produits...': 'ابحث عن المنتجات...',
    'Tous les produits': 'جميع المنتجات',
    'Herbes aromatiques': 'أعشاب عطرية',
    'Packs hebdomadaires': 'الباقات الأسبوعية',
    'Aucun produit trouvé': 'لم يتم العثور على منتجات',
    'Chargement des produits…': 'جارٍ تحميل المنتجات…',
    'Impossible de charger les produits.': 'تعذّر تحميل المنتجات.',
    'Disponible': 'متوفر',
    'Stock limité': 'مخزون محدود',
    'Rupture de stock': 'نفد المخزون',
    'Aucun pack disponible': 'لا توجد باقات متاحة',
    'Épuisé': 'نفد',
    'Saison': 'موسم',
    'Hebdo': 'أسبوعي',
    'Offerte': 'مجاني',
    'Pack': 'باقة',
    'Dans le panier': 'في السلة',

    // Détail produit
    'Description': 'الوصف',
    'Directement du producteur': 'مباشرة من المنتِج',
    'Avis clients': 'آراء العملاء',
    'Impossible de charger les avis.': 'تعذّر تحميل التقييمات.',
    'Votre avis': 'تقييمك',
    'Modifier': 'تعديل',
    'Supprimer': 'حذف',
    'Donner votre avis': 'اترك تقييمك',
    'Partagez votre expérience (optionnel)…': 'شارك تجربتك (اختياري)…',
    "Publier l'avis": 'نشر التقييم',
    'Aucun avis pour ce produit.': 'لا توجد تقييمات لهذا المنتج.',
    'Modifier votre avis': 'تعديل تقييمك',
    'Mettre à jour': 'تحديث',
    'Connectez-vous pour laisser un avis': 'سجّل الدخول لترك تقييم',
    'Veuillez choisir une note (1 à 5 étoiles).': 'يرجى اختيار تقييم (من 1 إلى 5 نجوم).',
    'Publication…': 'جارٍ النشر…',
    'Avis publié avec succès !': 'تم نشر التقييم بنجاح !',
    'Erreur lors de la publication.': 'خطأ أثناء النشر.',
    'Erreur réseau.': 'خطأ في الشبكة.',
    'Avis supprimé.': 'تم حذف التقييم.',
    'Erreur.': 'خطأ.',

    // Panier / favoris (toasts)
    'Ajouté au panier !': 'أُضيف إلى السلة !',
    'Ajouté aux favoris !': 'أُضيف إلى المفضلة !',
    'Retiré des favoris': 'أُزيل من المفضلة',
    'Article retiré du panier': 'تمت إزالة العنصر من السلة',
    'Boîte retirée du panier': 'تمت إزالة الصندوق من السلة',
    'Votre panier est vide.': 'سلتك فارغة.',
    'Cette boîte est épuisée.': 'هذا الصندوق نفد.',

    // Favoris (page)
    'Favoris': 'المفضلة',
    'Aucun favori': 'لا توجد مفضلات',
    'Ajoutez des produits à vos favoris depuis la boutique': 'أضف منتجات إلى مفضلتك من المتجر',
    'Parcourir la boutique': 'تصفّح المتجر',
    'Voir le produit': 'عرض المنتج',
    'Retirer des favoris': 'إزالة من المفضلة',

    // Panier (page)
    'Mon Panier': 'سلتي',
    'Votre sélection': 'اختيارك',
    'Produits frais, prix transparents': 'منتجات طازجة، أسعار شفافة',
    'Sous-total': 'المجموع الفرعي',
    'Gratuite': 'مجاني',
    'Total': 'المجموع',
    'Passer la commande': 'إتمام الطلب',
    'Panier vide': 'السلة فارغة',
    'Ajoutez des produits depuis la boutique': 'أضف منتجات من المتجر',
    'boîte': 'صندوق',

    // Checkout
    'Panier': 'السلة',
    'Confirmation': 'تأكيد',
    'Récapitulatif': 'الملخص',
    'Informations de livraison': 'معلومات التوصيل',
    'Nom complet *': 'الاسم الكامل *',
    'Téléphone *': 'الهاتف *',
    'Wilaya *': 'الولاية *',
    'Commune': 'البلدية',
    'Adresse précise (optionnel)': 'العنوان الدقيق (اختياري)',
    'Paiement à la livraison': 'الدفع عند الاستلام',
    'Payez en espèces lors de la réception de votre commande': 'ادفع نقداً عند استلام طلبك',
    'Total à payer': 'المبلغ الإجمالي',
    'Confirmer la commande': 'تأكيد الطلب',
    'Commande confirmée !': 'تم تأكيد الطلب !',
    'Nous préparons votre commande avec soin.': 'نُحضّر طلبك بعناية.',
    'Numéro de commande': 'رقم الطلب',
    'Paiement en espèces à la livraison (main à main)': 'الدفع نقداً عند الاستلام (يداً بيد)',
    'Continuer mes achats': 'متابعة التسوّق',
    'Veuillez entrer votre nom.': 'يرجى إدخال اسمك.',
    'Veuillez entrer votre téléphone.': 'يرجى إدخال هاتفك.',
    'Veuillez entrer votre wilaya.': 'يرجى إدخال ولايتك.',
    'Traitement…': 'جارٍ المعالجة…',
    'Erreur lors de la commande.': 'خطأ أثناء الطلب.',
    'Erreur réseau. Veuillez réessayer.': 'خطأ في الشبكة. يرجى المحاولة مجدداً.',

    // Profil consommateur / commandes
    'Téléphone': 'الهاتف',
    'Wilaya': 'الولاية',
    'Mes commandes': 'طلباتي',
    'Chargement…': 'جارٍ التحميل…',
    'Se déconnecter': 'تسجيل الخروج',
    "Aucune commande pour l'instant.": 'لا توجد طلبات حالياً.',
    'Erreur de chargement.': 'خطأ في التحميل.',
    'Nouvelle': 'جديدة',
    'En préparation': 'قيد التحضير',
    'Livrée': 'تم التوصيل',
    'Annulée': 'ملغاة',
    'Inconnue': 'غير معروفة',

    // Authentification (modale + auth.js)
    'Connectez-vous pour continuer': 'سجّل الدخول للمتابعة',
    'Créer un compte': 'إنشاء حساب',
    'Numéro de téléphone ou email': 'رقم الهاتف أو البريد الإلكتروني',
    'Mot de passe': 'كلمة المرور',
    'Nom complet': 'الاسم الكامل',
    'Numéro de téléphone': 'رقم الهاتف',
    'Email (optionnel)': 'البريد الإلكتروني (اختياري)',
    'Wilaya (obligatoire pour les agriculteurs)': 'الولاية (إلزامية للمزارعين)',
    'Rôle': 'الدور',
    'Consumer': 'مستهلك',
    'Farmer': 'مزارع',
    'Se connecter': 'دخول',
    'Créer le compte': 'إنشاء الحساب',
    'Vous pouvez parcourir les produits sans compte. Le compte est requis pour commander.': 'يمكنك تصفّح المنتجات دون حساب. الحساب مطلوب لإتمام الطلب.',
    'Inscrivez-vous comme Farmer ou Consumer': 'سجّل كمزارع أو مستهلك',
    'Veuillez renseigner votre identifiant et mot de passe.': 'يرجى إدخال معرّفك وكلمة المرور.',
    'Connexion réussie.': 'تم تسجيل الدخول بنجاح.',
    'Identifiants incorrects.': 'بيانات الدخول غير صحيحة.',
    'Erreur de connexion. Vérifiez votre réseau.': 'خطأ في الاتصال. تحقّق من شبكتك.',
    'Nom, téléphone et mot de passe sont obligatoires.': 'الاسم والهاتف وكلمة المرور إلزامية.',
    'La wilaya est obligatoire pour les agriculteurs.': 'الولاية إلزامية للمزارعين.',
    'Compte créé avec succès.': 'تم إنشاء الحساب بنجاح.',
    "Erreur lors de l'inscription.": 'خطأ أثناء التسجيل.',
    'Déconnecté avec succès.': 'تم تسجيل الخروج بنجاح.',

    // Vidéo
    'Fraîcheur directe des fermes algériennes à votre porte': 'نضارة مباشرة من المزارع الجزائرية إلى باب منزلك',

    // ── Tableau de bord agriculteur ──
    'Ajouter un produit': 'إضافة منتج',
    'Mes produits': 'منتجاتي',
    'Commandes reçues': 'الطلبات الواردة',
    'Mon profil': 'ملفي الشخصي',
    'Déconnexion': 'تسجيل الخروج',
    'Nouveau produit': 'منتج جديد',
    'Renseignez les informations ci-dessous et publiez votre offre.': 'أدخل المعلومات أدناه وانشر عرضك.',
    'Informations produit': 'معلومات المنتج',
    'Nom du produit': 'اسم المنتج',
    'Ex : Tomates cerises bio': 'مثال: طماطم كرزية عضوية',
    'Type de tarification': 'نوع التسعير',
    'Par kg': 'بالكيلوغرام',
    'Par bouquet': 'بالباقة',
    'Personnalisé': 'مخصّص',
    'Libellé de quantité': 'وصف الكمية',
    'Ex : caisse de 5 kg': 'مثال: صندوق 5 كغ',
    'Tarification & stock': 'التسعير والمخزون',
    'Prix unitaire': 'سعر الوحدة',
    'Quantité disponible': 'الكمية المتوفرة',
    "Origine, qualité, conseils d'utilisation…": 'المصدر، الجودة، نصائح الاستخدام…',
    'Photo du produit': 'صورة المنتج',
    "URL de l'image": 'رابط الصورة',
    "Ou choisir depuis l'appareil": 'أو اختر من الجهاز',
    'Ajouter le produit': 'إضافة المنتج',
    'Annuler': 'إلغاء',
    'Stock': 'المخزون',
    'Modifiez ou supprimez vos offres en un clic.': 'عدّل أو احذف عروضك بنقرة واحدة.',
    'Commandes': 'الطلبات',
    'Détail des achats effectués sur vos produits, avec les coordonnées de chaque client.': 'تفاصيل المشتريات على منتجاتك، مع بيانات كل عميل.',
    'Mon profil agriculteur': 'ملفي الشخصي كمزارع',
    'Vos informations personnelles et statistiques de vente.': 'معلوماتك الشخصية وإحصاءات المبيعات.',
    'Agriculteur': 'مزارع',
    'Identifiant :': 'المعرّف :',
    'Produits actifs': 'المنتجات النشطة',
    'Chiffre d\'affaires': 'رقم الأعمال',
    'Enregistrer': 'حفظ',
    'Mode modification activé.': 'تم تفعيل وضع التعديل.',
    'Supprimer ce produit définitivement ?': 'حذف هذا المنتج نهائياً؟',
    'Erreur lors de la suppression.': 'خطأ أثناء الحذف.',
    'Produit supprimé.': 'تم حذف المنتج.',
    'Erreur réseau lors de la suppression.': 'خطأ في الشبكة أثناء الحذف.',
    'Nom, prix et quantité sont obligatoires.': 'الاسم والسعر والكمية إلزامية.',
    'Ajoutez le libellé de quantité.': 'أضف وصف الكمية.',
    'Libellé bouquet manquant.': 'وصف الباقة مفقود.',
    'Erreur lors de la sauvegarde.': 'خطأ أثناء الحفظ.',
    'Produit modifié avec succès.': 'تم تعديل المنتج بنجاح.',
    'Produit ajouté avec succès.': 'تمت إضافة المنتج بنجاح.',
    'Erreur réseau. Réessayez.': 'خطأ في الشبكة. أعد المحاولة.',
    'Aucun produit ajouté pour le moment.': 'لم تتم إضافة أي منتج بعد.',
    'Aucune commande pour vos produits pour le moment.': 'لا توجد طلبات على منتجاتك حالياً.',
    'Produit': 'المنتج',
    'Quantité': 'الكمية',
    'Votre total sur cette commande': 'إجماليك على هذا الطلب',
    'En attente': 'قيد الانتظار',
    'Confirmée': 'مؤكدة',
    'En cours': 'قيد المعالجة',
    'Expédiée': 'تم الشحن',
  };

  // ── Patterns for dynamic strings containing numbers / names ──
  // Applied only to a whole trimmed text node when no exact match exists.
  const PATTERNS = [
    { re: /^(Disponible|Stock limité|Rupture de stock) · (.+)$/, t: (m) => (I18N[m[1]] || m[1]) + ' · ' + m[2] },
    { re: /^\((\d+) avis\)$/, t: (m) => '(' + m[1] + ' تقييم)' },
    { re: /^([\d\s.,]+) produits? inclus$/, t: (m) => m[1].trim() + ' منتج مضمَّن' },
    { re: /^([\d\s.,]+) produits?$/, t: (m) => m[1].trim() + ' منتج' },
    { re: /^\((\d+) articles?\)$/, t: (m) => '(' + m[1] + ' عنصر)' },
    { re: /^(\d+) articles?$/, t: (m) => m[1] + ' عنصر' },
    { re: /^(\d+) packs?$/, t: (m) => m[1] + ' باقة' },
    { re: /^(\d+) boîtes?$/, t: (m) => m[1] + ' صندوق' },
    { re: /^(\d+) disponibles?$/, t: (m) => m[1] + ' متوفر' },
    { re: /^(\d+) restante\(s\)$/, t: (m) => 'بقي ' + m[1] },
    { re: /^Plus que (\d+) !$/, t: (m) => 'بقي ' + m[1] + ' فقط !' },
    { re: /^Expire dans (\d+) jours?$/, t: (m) => 'ينتهي خلال ' + m[1] + ' يوم' },
    { re: /^Disponible encore (\d+) jours?$/, t: (m) => 'متوفر ' + m[1] + ' يوم إضافي' },
    { re: /^(\d+) commandes? passées?$/, t: (m) => m[1] + ' طلب' },
    { re: /^Région de (.+) · Sans intermédiaire$/, t: (m) => 'منطقة ' + m[1] + ' · بدون وسيط' },
    { re: /^(.+) ajoutée? au panier !$/, t: (m) => 'تمت إضافة ' + m[1] + ' إلى السلة !' },
    { re: /^Bienvenue (.+) !$/, t: (m) => 'مرحباً ' + m[1] + ' !' },
  ];

  function translateString(fr) {
    if (Object.prototype.hasOwnProperty.call(I18N, fr)) return I18N[fr];
    for (const p of PATTERNS) {
      const m = fr.match(p.re);
      if (m) return p.t(m);
    }
    return fr; // no translation found
  }

  // ── State ──
  let _lang = 'fr';
  const _orig = new Map();       // textNode -> original raw value
  let _observer = null;

  // ── Text-node translation ──
  function translateTextNode(node) {
    const raw = _orig.has(node) ? _orig.get(node) : node.nodeValue;
    const trimmed = raw.trim();
    if (!trimmed) return;
    const tr = translateString(trimmed);
    if (tr === trimmed) return;
    if (!_orig.has(node)) _orig.set(node, raw);
    node.nodeValue = raw.replace(trimmed, tr);
  }

  function eachTextNode(root, fn) {
    if (root.nodeType === 3) { fn(root); return; }
    if (root.nodeType !== 1) return;
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode(n) {
        const p = n.parentElement;
        if (!p) return NodeFilter.FILTER_REJECT;
        if (SKIP_TAGS.has(p.tagName)) return NodeFilter.FILTER_REJECT;
        if (p.closest('.sf-lang-toggle,[data-sf-skip]')) return NodeFilter.FILTER_REJECT;
        if (!n.nodeValue || !n.nodeValue.trim()) return NodeFilter.FILTER_SKIP;
        return NodeFilter.FILTER_ACCEPT;
      },
    });
    const nodes = [];
    let cur;
    while ((cur = walker.nextNode())) nodes.push(cur);
    nodes.forEach(fn);
  }

  // ── Attribute translation (placeholder / title) ──
  function tAttr(el, attr, dsKey) {
    if (!el.hasAttribute(attr)) return;
    if (el.closest('.sf-lang-toggle,[data-sf-skip]')) return;
    const stored = el.dataset[dsKey];
    const orig = stored !== undefined ? stored : el.getAttribute(attr);
    const trimmed = orig.trim();
    if (!trimmed) return;
    const tr = translateString(trimmed);
    if (tr === trimmed) return;
    if (stored === undefined) el.dataset[dsKey] = orig;
    el.setAttribute(attr, orig.replace(trimmed, tr));
  }

  function translateAttrsIn(root) {
    const targets = [];
    if (root.nodeType === 1) {
      if (root.hasAttribute('placeholder') || root.hasAttribute('title')) targets.push(root);
      root.querySelectorAll('[placeholder],[title]').forEach((el) => targets.push(el));
    }
    targets.forEach((el) => {
      tAttr(el, 'placeholder', 'sfPh');
      tAttr(el, 'title', 'sfTitle');
    });
  }

  // ── Full page translate / restore ──
  function translatePage() {
    eachTextNode(document.body, translateTextNode);
    translateAttrsIn(document.body);
  }

  function restorePage() {
    _orig.forEach((raw, node) => { if (node.isConnected) node.nodeValue = raw; });
    _orig.clear();
    document.querySelectorAll('[data-sf-ph]').forEach((el) => {
      el.setAttribute('placeholder', el.dataset.sfPh);
      delete el.dataset.sfPh;
    });
    document.querySelectorAll('[data-sf-title]').forEach((el) => {
      el.setAttribute('title', el.dataset.sfTitle);
      delete el.dataset.sfTitle;
    });
  }

  // ── Mutation observer: translate dynamically added content ──
  function startObserver() {
    if (_observer) return;
    _observer = new MutationObserver((muts) => {
      for (const m of muts) {
        m.addedNodes.forEach((node) => {
          if (node.nodeType === 3) {
            const p = node.parentElement;
            if (p && !SKIP_TAGS.has(p.tagName) && !p.closest('.sf-lang-toggle,[data-sf-skip]')) {
              translateTextNode(node);
            }
          } else if (node.nodeType === 1) {
            if (node.closest && node.closest('.sf-lang-toggle,[data-sf-skip]')) return;
            eachTextNode(node, translateTextNode);
            translateAttrsIn(node);
          }
        });
      }
    });
    _observer.observe(document.body, { childList: true, subtree: true });
  }

  function stopObserver() {
    if (_observer) { _observer.disconnect(); _observer = null; }
  }

  // ── Apply language ──
  function setLang(lang) {
    _lang = lang === 'ar' ? 'ar' : 'fr';
    const html = document.documentElement;
    if (_lang === 'ar') {
      html.setAttribute('lang', 'ar');
      html.setAttribute('dir', 'rtl');
      document.body.classList.add('sf-rtl');
      translatePage();
      startObserver();
    } else {
      stopObserver();
      html.setAttribute('lang', 'fr');
      html.setAttribute('dir', 'ltr');
      document.body.classList.remove('sf-rtl');
      restorePage();
    }
    try { localStorage.setItem(STORAGE_KEY, _lang); } catch (e) { /* ignore */ }
    updateButton();
  }

  function toggle() { setLang(_lang === 'ar' ? 'fr' : 'ar'); }

  // ── Floating toggle button ──
  function updateButton() {
    const b = document.querySelector('.sf-lang-toggle');
    if (!b) return;
    b.innerHTML = _lang === 'ar'
      ? '<i class="fas fa-globe"></i><span>FR</span>'
      : '<i class="fas fa-globe"></i><span>العربية</span>';
  }

  function buildButton() {
    if (document.querySelector('.sf-lang-toggle')) return;
    const b = document.createElement('button');
    b.className = 'sf-lang-toggle';
    b.type = 'button';
    b.setAttribute('data-sf-skip', '');
    b.setAttribute('aria-label', 'Changer de langue / تغيير اللغة');
    b.addEventListener('click', toggle);
    document.body.appendChild(b);
    updateButton();
  }

  // ── Inject CSS + Arabic font ──
  function injectAssets() {
    if (document.getElementById('sf-i18n-style')) return;
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap';
    document.head.appendChild(link);

    const st = document.createElement('style');
    st.id = 'sf-i18n-style';
    st.textContent = [
      '.sf-lang-toggle{position:fixed;z-index:150;bottom:88px;left:18px;display:inline-flex;',
      'align-items:center;gap:7px;padding:9px 15px;border-radius:999px;',
      'border:1px solid rgba(255,255,255,.3);background:linear-gradient(135deg,#1e6b3c,#27a163);',
      "color:#fff;font-family:'Nunito','Tajawal',sans-serif;font-weight:800;font-size:13px;line-height:1;",
      'box-shadow:0 8px 24px rgba(15,58,31,.32);cursor:pointer;',
      'transition:transform .15s ease, box-shadow .15s ease;}',
      '.sf-lang-toggle:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(15,58,31,.42);}',
      '.sf-lang-toggle:active{transform:scale(.94);}',
      '.sf-lang-toggle i{font-size:13px;}',
      'html[dir="rtl"] .sf-lang-toggle{left:auto;right:18px;}',
      // Arabic font in RTL mode (icons keep their own Font Awesome family)
      "html[dir=\"rtl\"] body,html[dir=\"rtl\"] .font-body,html[dir=\"rtl\"] .font-display,",
      "html[dir=\"rtl\"] .fd-sidebar,html[dir=\"rtl\"] .fd-content,html[dir=\"rtl\"] input,",
      "html[dir=\"rtl\"] textarea,html[dir=\"rtl\"] select,html[dir=\"rtl\"] button{",
      "font-family:'Tajawal','Nunito',sans-serif;}",
    ].join('');
    document.head.appendChild(st);
  }

  // ── Init ──
  function init() {
    injectAssets();
    buildButton();
    let saved = null;
    try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) { /* ignore */ }
    if (saved === 'ar') setLang('ar');
    else updateButton();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose for manual use / debugging
  window.SFLang = { set: setLang, toggle: toggle, current: () => _lang };
})();
