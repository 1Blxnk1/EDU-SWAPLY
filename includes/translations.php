<?php
/**
 * Swaply - Lightweight Translation System
 * Unorthodox approach: PHP array-based i18n with session-based language switching
 * Covers 3 official SA languages: English, isiZulu, Sepedi
 */

// Available languages
$LANGUAGES = [
    'en' => ['name' => 'English', 'code' => 'EN'],
    'zu' => ['name' => 'isiZulu', 'code' => 'ZU'],
    'nso' => ['name' => 'Sepedi', 'code' => 'NSO']
];

// Translation dictionary for multilingual support (Goal 7)
// Built this after realising gettext extension wasn't available on XAMPP
// Used PHP arrays instead - simpler but works fine for 3 languages
$TRANSLATIONS = [
    // Navigation
    'nav_home' => [
        'en' => 'Home',
        'zu' => 'Ikhaya',
        'nso' => 'Lehae'
    ],
    'cart_one_seller_only' => [
        'en' => 'You already have items from another seller. Finish that order or empty your cart first.',
        'zu' => 'Unezimpahla kakade kothile odayisayo. Qedela leli oda noma uphakulule inqola yakho kuqala.',
        'nso' => 'O na le dilo go tšwa go morekiši o mongwe. Feleletša oda yeo goba o ntšhe dilo ka karatseng pele.'
    ],
    'nav_shop' => [
        'en' => 'Shop',
        'zu' => 'Thenga',
        'nso' => 'Reka'
    ],
    'nav_cart' => [
        'en' => 'Cart',
        'zu' => 'Inqola',
        'nso' => 'Karatse'
    ],
    'nav_login' => [
        'en' => 'Login',
        'zu' => 'Ngena',
        'nso' => 'Tsena'
    ],
    'nav_register' => [
        'en' => 'Register',
        'zu' => 'Bhalisa',
        'nso' => 'Ngwadi&scaron;a'
    ],
    'nav_logout' => [
        'en' => 'Logout',
        'zu' => 'Phuma',
        'nso' => 'T&scaron;wa'
    ],

    // Hero Section
    'hero_title' => [
        'en' => 'Buy & Sell in Your Community',
        'zu' => 'Thenga & Thengisa emphakathini wakho',
        'nso' => 'Reka & Reke&scaron;a Mogopolong Wa Gago'
    ],
    'hero_subtitle' => [
        'en' => 'Swaply connects township traders with buyers safely. Verified sellers, secure payments, no cash risks.',
        'zu' => 'I-Swaply ixhumisa abathengisi bomphakathi nabathengi ngokuphepha. Abathengisi baqinisekisiwe, izikhokho ezikhuselekile, akukho ingozi yemali.',
        'nso' => 'Swaply e kopant&scaron;a bareki&scaron;et&scaron;i ba motse le bareki ka t&scaron;hirelet&scaron;o. Bareki&scaron;et&scaron;i ba kgonthi&scaron;i&scaron;it&scaron;wego, ditefo t&scirc;a t&scaron;hirelet&scaron;o, ga go na kotsi ya t&scaron;helete.'
    ],
    'hero_btn_shop' => [
        'en' => 'Start Shopping',
        'zu' => 'Qala Ukuthenga',
        'nso' => 'Thoma GoReka'
    ],
    'hero_btn_sell' => [
        'en' => 'Become a Seller',
        'zu' => 'Yiba Umthengisi',
        'nso' => 'Ba Moki&scaron;et&scaron;i wa Dithoto'
    ],
    'hero_stats' => [
        'en' => '{products} products from {sellers} trusted sellers',
        'zu' => 'Imikhiqizo engu-{products} abathengisi abangu-{sellers} abathembekile',
        'nso' => 'Dithoto t&scaron;e {products} go t&scaron;wa go bareki&scaron;et&scaron;i ba {sellers} ba bont&scaron;hit&scaron;wego'
    ],

    // Categories
    'cat_browse' => [
        'en' => 'Browse Categories',
        'zu' => 'Phequlula Izigaba',
        'nso' => 'Lebelela Magoro'
    ],

    // How It Works
    'hiw_title' => [
        'en' => 'How Swaply Works',
        'zu' => 'Ukusebenza Kwe-Swaply',
        'nso' => 'Ka Moo Swaply e &Scaron;omago'
    ],
    'hiw_step1_title' => [
        'en' => '1. Create Account',
        'zu' => '1. Yenza I-akhawunti',
        'nso' => '1. Hlama Akhaonte'
    ],
    'hiw_step1_desc' => [
        'en' => 'Register as a buyer or seller. Verify your email to get started.',
        'zu' => 'Bhalisa njengomthengi noma umthengisi. Qinisekisa i-imeyili yakho ukuze uqalise.',
        'nso' => 'Ngwadi&scaron;a bjalo ka moreki goba moki&scaron;et&scaron;i. Kgonthi&scaron;ti&scaron;a imeile ya gago gore o thome.'
    ],
    'hiw_step2_title' => [
        'en' => '2. Buy or Sell',
        'zu' => '2. Thenge noma Thengisa',
        'nso' => '2. Reka goba Reke&scaron;a'
    ],
    'hiw_step2_desc' => [
        'en' => 'Browse products from verified sellers or list your own items.',
        'zu' => 'Phequlula imikhiqizo yabathengisi abaqinisekisiwe noma ubeke izinto zakho.',
        'nso' => 'Lebelela dithoto t&scaron;a bareki&scaron;et&scaron;i ba kgonthi&scaron;it&scaron;wego goba bont&scaron;ha t&scaron;a gago.'
    ],
    'hiw_step3_title' => [
        'en' => '3. Secure Payment',
        'zu' => '3. Inkokhelo Ekhuselekile',
        'nso' => '3. T&scaron;helete ya T&scaron;hirelet&scaron;o'
    ],
    'hiw_step3_desc' => [
        'en' => 'Pay safely through our platform. No cash, no risk.',
        'zu' => 'Khokha ngokuphepha ngaphakathi kwephulatifomu yethu. Awukho umzuzu, akukho ingozi.',
        'nso' => 'Leka ka t&scaron;hirelet&scaron;o ka mo platform ya rena. Ga go na t&scaron;helete ya lenane, ga go na kotsi.'
    ],

    // Featured Products
    'fp_title' => [
        'en' => 'Featured Products',
        'zu' => 'Imikhiqizo Ekhombisiwe',
        'nso' => 'Dithoto t&scaron;e T&scaron;edi&scaron;it&scaron;wego'
    ],
    'fp_view_all' => [
        'en' => 'View All',
        'zu' => 'Buka Konke',
        'nso' => 'Lebelela Ka Moka'
    ],

    // Product Card / Listing
    'btn_view_product' => [
        'en' => 'View Product',
        'zu' => 'Buka Umkhiqizo',
        'nso' => 'Lebelela T&scaron;heto'
    ],
    'by_seller' => [
        'en' => 'by {name}',
        'zu' => 'ngu-{name}',
        'nso' => 'ke {name}'
    ],
    'in_stock' => [
        'en' => '{count} in stock',
        'zu' => '{count} okusekho',
        'nso' => '{count} di le gare'
    ],
    'sold_out' => [
        'en' => 'Sold Out',
        'zu' => 'Kuphelile',
        'nso' => 'Di Felet&scaron;e'
    ],

    // Cart
    'cart_title' => [
        'en' => 'Shopping Cart',
        'zu' => 'Inqola Yokuthenga',
        'nso' => 'Karatse ya goReka'
    ],
    'cart_empty' => [
        'en' => 'Your cart is empty',
        'zu' => 'Inqola yakho ayinalutho',
        'nso' => 'Karatse ya gago ga e na selo'
    ],
    'cart_browse' => [
        'en' => 'Browse our products and add items to your cart.',
        'zu' => 'Phequlula imikhiqizo yethu bese ungeza izinto enqolweni yakho.',
        'nso' => 'Lebelela dithoto t&scaron;a rena gomme o t&scaron;ee dilo karat&scaron;eng ya gago.'
    ],
    'cart_continue' => [
        'en' => 'Continue Shopping',
        'zu' => 'Qhubeka Uthenga',
        'nso' => 'T&scaron;wela Pele ka goReka'
    ],
    'order_summary' => [
        'en' => 'Order Summary',
        'zu' => 'Isifinyezo Se-oda',
        'nso' => 'Kakaret&scaron;o ya Taelo'
    ],
    'subtotal' => [
        'en' => 'Subtotal',
        'zu' => 'Isamba',
        'nso' => 'Palomoka'
    ],
    'shipping' => [
        'en' => 'Shipping',
        'zu' => 'Ukuhanjiswa',
        'nso' => 'Pamp&scaron;i'
    ],
    'free' => [
        'en' => 'Free',
        'zu' => 'Mahhala',
        'nso' => 'Mahala'
    ],
    'total' => [
        'en' => 'Total',
        'zu' => 'Isamba Sonke',
        'nso' => 'Palomoka Ka Moka'
    ],
    'checkout_btn' => [
        'en' => 'Proceed to Checkout',
        'zu' => 'Qhubela Ekukhokheni',
        'nso' => 'T&scaron;wela Pele go lek&scaron;a'
    ],

    // Discount (Goal 3)
    'discount_first' => [
        'en' => 'First Order Discount',
        'zu' => 'Isaphulelo Sokuqala',
        'nso' => 'T&scaron;helete ye Taelo ya Pele'
    ],
    'discount_welcome' => [
        'en' => 'Welcome! You got R50 off your first order!',
        'zu' => 'Siyakwamukela! Uthole i-R50 phansi ku-oda lakho lokuqala!',
        'nso' => 'O a Amogelwa! O hwet&scaron;a R50 tlase taelong ya gago ya pele!'
    ],

    // Checkout
    'checkout_title' => [
        'en' => 'Checkout',
        'zu' => 'Ukuhlola',
        'nso' => 'Lek&scaron;a'
    ],
    'shipping_info' => [
        'en' => 'Shipping Information',
        'zu' => 'Ulwazi Lokuthunyelwa',
        'nso' => 'Tshedimo&scaron;o ya Pamp&scaron;i'
    ],
    'delivery_address' => [
        'en' => 'Delivery Address',
        'zu' => 'Ikheli Lokuthunyelwa',
        'nso' => 'Aterese ya Pamp&scaron;i'
    ],
    'address_placeholder' => [
        'en' => 'Street address, suburb, city, postal code',
        'zu' => 'Ikheli, isabelo, idolobha, ikhodi yeposi',
        'nso' => 'Aterese ya mose, seletemere, toropo, khoutu ya poso'
    ],
    'payment_method' => [
        'en' => 'Payment Method',
        'zu' => 'Indlela Yokukhokha',
        'nso' => 'Mokgwa wa T&scaron;helete'
    ],
    'pay_card' => [
        'en' => 'Credit/Debit Card (Secure Payment)',
        'zu' => 'Ikhadi Lesikweletu/Isikweletu (Inkokhelo Ekhuselekile)',
        'nso' => 'Kharata ya Mok&scaron;edi/Kharata ya Debit (T&scaron;helete ya T&scaron;hirelet&scaron;o)'
    ],
    'pay_demo' => [
        'en' => 'This is a demo environment. No real payment will be processed.',
        'zu' => 'Lesi siyimvelo yedemo. Akukho khokho langempela ozolungiswa.',
        'nso' => 'Ye le demo. Ga go na t&scaron;helete ya kgonthe yeo e &scaron;omi&scaron;it&scaron;wego.'
    ],
    'pay_btn' => [
        'en' => 'Pay {amount}',
        'zu' => 'Khokha {amount}',
        'nso' => 'Leka {amount}'
    ],

    // Auth Pages
    'auth_register_title' => [
        'en' => 'Create Your Account',
        'zu' => 'Yenza I-akhawunti Yakho',
        'nso' => 'Hlama Akhaonte ya Gago'
    ],
    'auth_login_title' => [
        'en' => 'Login to Swaply',
        'zu' => 'Ngena ku-Swaply',
        'nso' => 'Tsena go Swaply'
    ],
    'buy_products' => [
        'en' => 'Buy Products',
        'zu' => 'Thenga Imikhiqizo',
        'nso' => 'Reka Dithoto'
    ],
    'sell_products' => [
        'en' => 'Sell Products',
        'zu' => 'Thengisa Imikhiqizo',
        'nso' => 'Reke&scaron;a Dithoto'
    ],
    'full_name' => [
        'en' => 'Full Name',
        'zu' => 'Igama Eliphelele',
        'nso' => 'Leina ka Botlalo'
    ],
    'email' => [
        'en' => 'Email Address',
        'zu' => 'Ikheli le-imeyili',
        'nso' => 'Aterese ya Imeile'
    ],
    'phone' => [
        'en' => 'Phone Number',
        'zu' => 'Inombolo Yocingo',
        'nso' => 'Nomoro ya Mogala'
    ],
    'phone_hint' => [
        'en' => '10-digit SA mobile number',
        'zu' => 'Inombolo yefoni ye-SA engama-10',
        'nso' => 'Nomoro ya selulari ya SA ya dijit&scaron;e t&scaron;e 10'
    ],
    'sa_id_number' => [
        'en' => 'South African ID Number',
        'zu' => 'Inombolo Yakho Ye-SA',
        'nso' => 'Nomoro ya Boit&scaron;eupe bja Afrika Borwa'
    ],
    'id_hint' => [
        'en' => '13-digit SA ID number. Required for buyer/seller verification and platform safety.',
        'zu' => 'Inombolo ye-SA engama-13. Idingekile ukuqinisekisa umthengi/umthengisi nokuphepha kwephulatifomu.',
        'nso' => 'Nomoro ya boit&scaron;eupe bja SA ya dijit&scaron;e t&scaron;e 13. Ea nyakega bjalo ka moreki/moki&scaron;et&scaron;i le bjalo ka t&scaron;hirelet&scaron;o ya platform.'
    ],
    'password' => [
        'en' => 'Password',
        'zu' => 'Iphasiwedi',
        'nso' => 'Lent&scaron;upheto'
    ],
    'password_hint' => [
        'en' => 'Minimum 8 characters, at least 1 number and 1 special character (!@#$%^&*)',
        'zu' => 'Ubuncane bobungozi be-8, okungenani inombolo eyi-1 nohlamvu olukhethekile eyi-1',
        'nso' => 'Bonyane mat&scaron;hwa a 8, bonyane nomoro ye 1 le t&scaron;hupamabaka ye 1'
    ],
    'confirm_password' => [
        'en' => 'Confirm Password',
        'zu' => 'Qinisekisa Iphasiwedi',
        'nso' => 'Kgonthi&scaron;ti&scaron;a Lent&scaron;upheto'
    ],
    'btn_create_account' => [
        'en' => 'Create Account',
        'zu' => 'Yenza I-akhawunti',
        'nso' => 'Hlama Akhaonte'
    ],
    'btn_login' => [
        'en' => 'Login',
        'zu' => 'Ngena',
        'nso' => 'Tsena'
    ],
    'have_account' => [
        'en' => 'Already have an account?',
        'zu' => 'Usuvenelo i-akhawunti?',
        'nso' => 'O &scaron;et&scaron;e o na le akhaonte?'
    ],
    'no_account' => [
        'en' => "Don't have an account?",
        'zu' => 'Awunalona i-akhawunti?',
        'nso' => 'Ga o na akhaonte?'
    ],
    'login_here' => [
        'en' => 'Login here',
        'zu' => 'Ngena lapha',
        'nso' => 'Tsena mo'
    ],
    'register_here' => [
        'en' => 'Register here',
        'zu' => 'Bhalisa lapha',
        'nso' => 'Ngwadi&scaron;a mo'
    ],

    // Verification (Goal 1 - ID verification)
    'verify_pending_title' => [
        'en' => 'Account Verification Pending',
        'zu' => 'Ukuqinisekisa I-akhawunti Kulindile',
        'nso' => 'Kgonthi&scaron;ti&scaron;o ya Akhaonte go e Leta'
    ],
    'verify_id_submitted' => [
        'en' => 'ID Submitted',
        'zu' => 'I-ID Ithunyelwe',
        'nso' => 'Boit&scaron;eupe bo Romilwe'
    ],
    'verify_pending_review' => [
        'en' => 'Pending Review',
        'zu' => 'Kulindelwe Ukuhlolwa',
        'nso' => 'Go Letet&scaron;we go Hloliwa'
    ],
    'continue_browsing' => [
        'en' => 'Continue Browsing',
        'zu' => 'Qhubeka Ukuphequlula',
        'nso' => 'T&scaron;wela Pele go Lebelela'
    ],
    'refresh_status' => [
        'en' => 'Refresh Status',
        'zu' => 'Vuselela Isimo',
        'nso' => 'Swafat&scaron;a Maemo'
    ],

    // Footer
    'footer_about' => [
        'en' => 'Connecting township traders with safe, secure e-commerce. Built for the community, by the community.',
        'zu' => 'Ixhumisa abathengisi bomphakathi nabothengi ngokuphepha. Yakhelwe emphakathini, ngemphakathi.',
        'nso' => 'Go kopant&scaron;a bareki&scaron;et&scaron;i ba motse le bareki ka t&scaron;hirelet&scaron;o. E ahelwe mot&scaron;eng, ke mot&scaron;e.'
    ],
    'footer_links' => [
        'en' => 'Quick Links',
        'zu' => 'Izixhumanisi Ezisheshayo',
        'nso' => 'Dikgokagano t&scaron;e Kape&scaron;o'
    ],
    'footer_support' => [
        'en' => 'Support',
        'zu' => 'Ukusekela',
        'nso' => 'T&scaron;heket&scaron;o'
    ],
    'footer_trust' => [
        'en' => 'Trust & Safety',
        'zu' => 'Ukuthembeka & Ukuphepha',
        'nso' => 'T&scaron;hemet&scaron;o & T&scaron;hirelet&scaron;o'
    ],
    'footer_verified' => [
        'en' => 'Verified sellers only',
        'zu' => 'Abathengisi abaqinisekisiwe kuphela',
        'nso' => 'Bareki&scaron;et&scaron;i ba kgonthi&scaron;it&scaron;wego fela'
    ],
    'footer_secure' => [
        'en' => 'Secure in-app payments',
        'zu' => 'Izikhokho ezikhuselekile ngaphakathi kwesicelo',
        'nso' => 'Ditefo t&scaron;a t&scaron;hirelet&scaron;o ka mo aplik&scaron;eneng'
    ],
    'footer_protection' => [
        'en' => 'Buyer protection on all orders',
        'zu' => 'Ukuvikelwa komthengi kuwo wonke ama-oda',
        'nso' => 'T&scaron;hirelet&scaron;o ya moreki go ditaelo ka moka'
    ],

    // Profile
    'my_profile' => [
        'en' => 'My Profile',
        'zu' => 'Iphrofayili Yami',
        'nso' => 'Profaele Ya Ka'
    ],
    'my_orders' => [
        'en' => 'My Orders',
        'zu' => 'Ama-oda Ami',
        'nso' => 'Ditaelo T&scaron;a Ka'
    ],
    'no_orders' => [
        'en' => 'No orders yet.',
        'zu' => 'Akukho oda okwamanje.',
        'nso' => 'Ga go na taelo ga bjale.'
    ],
    'start_shopping' => [
        'en' => 'Start shopping!',
        'zu' => 'Qala ukuthenga!',
        'nso' => 'Thoma go reka!'
    ],
    'write_review' => [
        'en' => 'Write Review',
        'zu' => 'Bhala Ukubuyekeza',
        'nso' => 'Ngwalla Kaketsi'
    ],
    'your_rating' => [
        'en' => 'Your Rating',
        'zu' => 'Isilinganiso Sakho',
        'nso' => 'Tekanyet&scaron;o ya Gago'
    ],
    'your_review' => [
        'en' => 'Your Review',
        'zu' => 'Ukubuyekeza Kwakho',
        'nso' => 'Kaketsi ya Gago'
    ],
    'submit_review' => [
        'en' => 'Submit Review',
        'zu' => 'Thumela Ukubuyekeza',
        'nso' => 'Romela Kaketsi'
    ],
    'cancel' => [
        'en' => 'Cancel',
        'zu' => 'Khansela',
        'nso' => 'Khansela'
    ],
    'reviewed' => [
        'en' => 'Reviewed',
        'zu' => 'Kubuyekeziwe',
        'nso' => 'Kaketsi e &Scaron;et&scaron;e e &Scaron;omi&scaron;it&scaron;we'
    ],
    'go_to_store' => [
        'en' => 'Go to Seller Dashboard',
        'zu' => 'Iya Ekuphathweni Kwezitolo',
        'nso' => 'Eya go Taolo ya Moki&scaron;et&scaron;i'
    ],
    'id_verified' => [
        'en' => 'ID Verified',
        'zu' => 'I-ID Iqinisekisiwe',
        'nso' => 'Boit&scaron;eupe bo Kgonthi&scaron;it&scaron;we'
    ],
    'id_pending' => [
        'en' => 'ID Pending',
        'zu' => 'I-ID Ilindele',
        'nso' => 'Boit&scaron;eupe bo a Letet&scaron;we'
    ],
    'check_status' => [
        'en' => 'Check status',
        'zu' => 'Hlola isimo',
        'nso' => 'Hlolela maemo'
    ],

    // Admin
    'admin_dashboard' => [
        'en' => 'Admin Dashboard',
        'zu' => 'Ideshibhodi Yomlawuli',
        'nso' => 'T&scaron;hupet&scaron;o ta Taolo'
    ],

    // General
    'complete' => [
        'en' => 'Complete',
        'zu' => 'Kugcwalisiwe',
        'nso' => 'Fedit&scaron;we'
    ],
    'pending' => [
        'en' => 'Pending',
        'zu' => 'Kulindele',
        'nso' => 'Go a Letet&scaron;wa'
    ],
    'order' => [
        'en' => 'Order',
        'zu' => 'I-oda',
        'nso' => 'Taelo'
    ],
    'status' => [
        'en' => 'Status',
        'zu' => 'Isimo',
        'nso' => 'Maemo'
    ],
    'date' => [
        'en' => 'Date',
        'zu' => 'Usuku',
        'nso' => 'Let&scaron;atsi'
    ],
    'actions' => [
        'en' => 'Actions',
        'zu' => 'Izenzo',
        'nso' => 'Dikgato'
    ],
    'save' => [
        'en' => 'Save',
        'zu' => 'Londoloza',
        'nso' => 'Boloka'
    ],
    'delete' => [
        'en' => 'Delete',
        'zu' => 'Susa',
        'nso' => 'Phumola'
    ],
    'update' => [
        'en' => 'Update',
        'zu' => 'Buyekeza',
        'nso' => 'Kaonefat&scaron;a'
    ],
    'add' => [
        'en' => 'Add',
        'zu' => 'Engeza',
        'nso' => 'T&scaron;ea'
    ],
    'search' => [
        'en' => 'Search',
        'zu' => 'Sesha',
        'nso' => 'Nyaka'
    ],
    'filter' => [
        'en' => 'Filter',
        'zu' => 'Hlunga',
        'nso' => 'Rarolla'
    ],
    'apply' => [
        'en' => 'Apply',
        'zu' => 'Faka',
        'nso' => 'Diragat&scaron;a'
    ],
    'clear_all' => [
        'en' => 'Clear All',
        'zu' => 'Susa Konke',
        'nso' => 'Tlosa Ka Moka'
    ],
    'welcome_back' => [
        'en' => 'Welcome back',
        'zu' => 'Siyakwamukela emuva',
        'nso' => 'O a Amogelwa gape'
    ],
    'logged_out' => [
        'en' => 'You have been logged out successfully',
        'zu' => 'Uphumile ngempumelelo',
        'nso' => 'O t&scaron;wile ka katlego'
    ],
    'yes' => [
        'en' => 'Yes',
        'zu' => 'Yebo',
        'nso' => 'Ee'
    ],
    'no' => [
        'en' => 'No',
        'zu' => 'Cha',
        'nso' => 'Aowa'
    ],
    'email_verify_title' => [
        'en' => 'Verify Your Email',
        'zu' => 'Qinisekisa I-imeyili Yakho',
        'nso' => 'Kgonthi&scaron;ti&scaron;a Imeile ya Gago'
    ],
    'email_verify_desc' => [
        'en' => 'We\'ve sent a verification code to your email. Enter it below to complete your registration.',
        'zu' => 'Sithumele ikhodi yokuqinisekisa ku-imeyili yakho. Faka ngezansi ukuze ugcwalise ukubhalisa kwakho.',
        'nso' => 'Re romet&scaron;e khoutu ya go kgonthi&scaron;ti&scaron;a go imeile ya gago. Eya ka tlase gore o fedit&scaron;e ngwadi&scaron;o ya gago.'
    ],
    'verification_code' => [
        'en' => 'Verification Code',
        'zu' => 'Ikhodi Yokuqinisekisa',
        'nso' => 'Khoutu ya go Kgonthi&scaron;ti&scaron;a'
    ],
    'btn_verify' => [
        'en' => 'Verify Email',
        'zu' => 'Qinisekisa I-imeyili',
        'nso' => 'Kgonthi&scaron;ti&scaron;a Imeile'
    ],
    'resend_code' => [
        'en' => 'Resend Code',
        'zu' => 'Thumela Kabusha Ikhodi',
        'nso' => 'Romela Khoutu Gape'
    ],
    'verify_email_banner' => [
        'en' => 'Please verify your email address. Check your inbox for the verification code.',
        'zu' => 'Siza uqinisekise ikheli lakho le-imeyili. Hlola ibhokisi lakho ngaphakathi ngekhodi yokuqinisekisa.',
        'nso' => 'Ka kgopelo kgonthi&scaron;ti&scaron;a aterese ya gago ya imeile. Hlola lepokisi la gago la t&scaron;enego bakeng sa khoutu ya go kgonthi&scaron;ti&scaron;a.'
    ],

    // Seller dashboard
    'sd_title' => [
        'en' => 'Seller Dashboard',
        'zu' => 'Ideshibhodi Yomthengisi',
        'nso' => 'Pokana ya Morekiši'
    ],
    'sd_overview' => [
        'en' => 'Overview',
        'zu' => 'Uhlolojikelele',
        'nso' => 'Tlhalošo'
    ],
    'sd_total_revenue' => [
        'en' => 'Total Revenue',
        'zu' => 'Imali Eyingeniso',
        'nso' => 'Tšhelete ka Moka'
    ],
    'sd_orders_received' => [
        'en' => 'Orders Received',
        'zu' => 'Ama-oda Atholiwe',
        'nso' => 'Diodara tše Amogetšwego'
    ],
    'sd_active_products' => [
        'en' => 'Active Products',
        'zu' => 'Imikhiqizo Esebenzayo',
        'nso' => 'Dithoto tše di šomago'
    ],
    'sd_rating' => [
        'en' => 'Rating (reviews)',
        'zu' => 'Isilinganiso (izibuyekezo)',
        'nso' => 'Tekanyetšo (ditshekatsheko)'
    ],
    'sd_my_products' => [
        'en' => 'My Products',
        'zu' => 'Imikhiqizo Yami',
        'nso' => 'Dithoto tša ka'
    ],
    'sd_add_product' => [
        'en' => 'Add Product',
        'zu' => 'Engeza Umkhiqizo',
        'nso' => 'Oketša Thoto'
    ],
    'sd_product_name' => [
        'en' => 'Product Name',
        'zu' => 'Igama Lomkhiqizo',
        'nso' => 'Leina la Thoto'
    ],
    'sd_product_image' => [
        'en' => 'Product Image',
        'zu' => 'Isithombe Somkhiqizo',
        'nso' => 'Seswantšho sa Thoto'
    ],
    'sd_image_hint' => [
        'en' => 'JPG, PNG or WebP, max 2MB. Optional.',
        'zu' => 'JPG, PNG noma WebP, okukhulu kakhulu 2MB. Akudingeki.',
        'nso' => 'JPG, PNG goba WebP, bogolo 2MB. Ga e gapeletšwe.'
    ],
    'sd_category' => [
        'en' => 'Category',
        'zu' => 'Isigaba',
        'nso' => 'Legoro'
    ],
    'sd_description' => [
        'en' => 'Description',
        'zu' => 'Incazelo',
        'nso' => 'Tlhaloso'
    ],
    'sd_price' => [
        'en' => 'Price',
        'zu' => 'Inani',
        'nso' => 'Theko'
    ],
    'sd_stock' => [
        'en' => 'Stock',
        'zu' => 'Isitokwe',
        'nso' => 'Setoko'
    ],
    'sd_no_products' => [
        'en' => 'No products yet. Add your first product above.',
        'zu' => 'Akukho mikhiqizo okwamanje. Engeza umkhiqizo wakho wokuqala ngenhla.',
        'nso' => 'Ga go na dithoto. Oketša thoto ya gago ya pele ka godimo.'
    ],
    'sd_recent_orders' => [
        'en' => 'Recent Orders',
        'zu' => 'Ama-oda Akamuva',
        'nso' => 'Diodara tša Morago'
    ],
    'sd_no_orders' => [
        'en' => 'No orders yet.',
        'zu' => 'Awekho ama-oda okwamanje.',
        'nso' => 'Ga go na diodara.'
    ],
    'sd_order_no' => [
        'en' => 'Order #',
        'zu' => 'I-oda #',
        'nso' => 'Odara #'
    ],
    'sd_buyer' => [
        'en' => 'Buyer',
        'zu' => 'Umthengi',
        'nso' => 'Moreki'
    ],
    'sd_recent_reviews' => [
        'en' => 'Recent Reviews',
        'zu' => 'Izibuyekezo Zakamuva',
        'nso' => 'Ditshekatsheko tša Morago'
    ],
    'sd_no_reviews' => [
        'en' => 'No reviews yet.',
        'zu' => 'Azikho izibuyekezo okwamanje.',
        'nso' => 'Ga go na ditshekatsheko.'
    ],
    'sd_confirm_delete' => [
        'en' => 'Delete this product?',
        'zu' => 'Susa lo mkhiqizo?',
        'nso' => 'Phumola thoto ye?'
    ],

    // Products listing page
    'prod_browse' => [
        'en' => 'Browse Products',
        'zu' => 'Bheka Imikhiqizo',
        'nso' => 'Lebelela Dithoto'
    ],
    'prod_found' => [
        'en' => 'products found',
        'zu' => 'imikhiqizo etholiwe',
        'nso' => 'dithoto tše hweditšwego'
    ],
    'prod_filters' => [
        'en' => 'Filters',
        'zu' => 'Izihlungi',
        'nso' => 'Difilitha'
    ],
    'prod_all_categories' => [
        'en' => 'All Categories',
        'zu' => 'Zonke Izigaba',
        'nso' => 'Magoro ka Moka'
    ],
    'prod_price_range' => [
        'en' => 'Price Range',
        'zu' => 'Inani',
        'nso' => 'Theko'
    ],
    'prod_min' => [
        'en' => 'Min',
        'zu' => 'Okuncane',
        'nso' => 'Bonnyane'
    ],
    'prod_max' => [
        'en' => 'Max',
        'zu' => 'Okukhulu',
        'nso' => 'Bogolo'
    ],
    'prod_sort_by' => [
        'en' => 'Sort By',
        'zu' => 'Hlunga Nge',
        'nso' => 'Beakanya ka'
    ],
    'prod_sort_newest' => [
        'en' => 'Newest First',
        'zu' => 'Okusha Kuqala',
        'nso' => 'Tše Mpsha Pele'
    ],
    'prod_sort_price_low' => [
        'en' => 'Price: Low to High',
        'zu' => 'Inani: Eliphansi Kuya Kwelinkulu',
        'nso' => 'Theko: Tlase go ya Godimo'
    ],
    'prod_sort_price_high' => [
        'en' => 'Price: High to Low',
        'zu' => 'Inani: Eliphezulu Kuya Kweliphansi',
        'nso' => 'Theko: Godimo go ya Tlase'
    ],
    'prod_sort_name' => [
        'en' => 'Name A-Z',
        'zu' => 'Igama A-Z',
        'nso' => 'Leina A-Z'
    ],
    'prod_apply' => [
        'en' => 'Apply Filters',
        'zu' => 'Sebenzisa Izihlungi',
        'nso' => 'Diriša Difilitha'
    ],
    'prod_clear' => [
        'en' => 'Clear All',
        'zu' => 'Sula Konke',
        'nso' => 'Phumola Tšohle'
    ],
    'prod_none' => [
        'en' => 'No products found',
        'zu' => 'Ayikho imikhiqizo etholakele',
        'nso' => 'Ga go na dithoto'
    ],
    'prod_none_hint' => [
        'en' => 'Try adjusting your search or filters.',
        'zu' => 'Zama ukushintsha usesho noma izihlungi zakho.',
        'nso' => 'Leka go fetoša patlišišo goba difilitha tša gago.'
    ],
    'prod_view_all' => [
        'en' => 'View All Products',
        'zu' => 'Bheka Yonke Imikhiqizo',
        'nso' => 'Lebelela Dithoto ka Moka'
    ],
    'prod_by' => [
        'en' => 'by',
        'zu' => 'ngu',
        'nso' => 'ka'
    ],
    'prod_in_stock' => [
        'en' => 'in stock',
        'zu' => 'kusesitokweni',
        'nso' => 'di gona'
    ],
    'prod_sold_out' => [
        'en' => 'Sold Out',
        'zu' => 'Kuthengiwe Konke',
        'nso' => 'Di Felile'
    ],
    'prod_view' => [
        'en' => 'View Product',
        'zu' => 'Bheka Umkhiqizo',
        'nso' => 'Lebelela Thoto'
    ]
];

/**
 * Get current language from session or cookie
 */
function getCurrentLanguage() {
    // Check session first
    if (isset($_SESSION['lang']) && array_key_exists($_SESSION['lang'], $GLOBALS['LANGUAGES'])) {
        return $_SESSION['lang'];
    }
    // Check cookie
    if (isset($_COOKIE['swaply_lang']) && array_key_exists($_COOKIE['swaply_lang'], $GLOBALS['LANGUAGES'])) {
        return $_COOKIE['swaply_lang'];
    }
    // Default to English
    return 'en';
}

/**
 * Set current language
 */
function setLanguage($lang) {
    if (array_key_exists($lang, $GLOBALS['LANGUAGES'])) {
        $_SESSION['lang'] = $lang;
        setcookie('swaply_lang', $lang, time() + 60*60*24*30, '/'); // 30 days
        return true;
    }
    return false;
}

/**
 * Translation function - __() shorthand
 * Looks up key in current language, falls back to English
 */
function __($key, $replacements = []) {
    $lang = getCurrentLanguage();
    $trans = $GLOBALS['TRANSLATIONS'];
    
    // Get translation, fallback to English, fallback to key itself
    $text = $trans[$key][$lang] ?? $trans[$key]['en'] ?? $key;
    
    // Replace placeholders {name}, {count}, {amount}, {products}, {sellers}
    foreach ($replacements as $placeholder => $value) {
        $text = str_replace('{' . $placeholder . '}', $value, $text);
    }
    
    return $text;
}

// Process language switch
if (isset($_GET['lang']) && setLanguage($_GET['lang'])) {
    // Redirect back to same page without lang param
    $url = strtok($_SERVER['REQUEST_URI'], '?');
    if (!empty($_SERVER['QUERY_STRING'])) {
        $params = [];
        parse_str($_SERVER['QUERY_STRING'], $params);
        unset($params['lang']);
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
    }
    redirect($url);
}
?>
