# Swaply

ITECA3 Project
Tadiwanashe Shonhiwa (Eduv4956423)

A C2C e-commerce platform for informal traders in South African townships.
Built with PHP, MySQL and vanilla HTML/CSS/JS.

## Running it locally

1. Install XAMPP and start Apache + MySQL.
2. Copy this folder into `htdocs/swaply/`.
3. Open phpMyAdmin and import `database/swaply.sql`.
4. Visit `http://localhost/swaply/`.

The DB connection is in `includes/db.php`. It uses env vars (`MYSQLHOST`,
`MYSQLUSER` etc) when those are set, and falls back to the default XAMPP
credentials otherwise.

## Demo accounts

```
Admin    admin@swaply.co.za    Password123!
Seller   thabo@email.com       Password123!
Buyer    nomsa@email.com       Password123!
```

## What's in here

Three user roles: buyer, seller and admin.

Buyers register, verify their email (a 6-digit code shown in a simulated
inbox) and their SA ID number (which waits for an admin to approve).
After verification they can browse, add to cart and check out. The cart
only accepts items from one seller at a time so we don't have to split a
payment between sellers.

Sellers list products (with image upload), see a dashboard showing
revenue, orders received, active products and their rating, and can
update the status of their orders as things get shipped and delivered.

Admins verify users, review flagged sellers (a seller is auto-flagged
when their average rating drops below 3 stars with 5 or more reviews),
drill into individual orders to see line items and the buyer's
shipping details, and resolve disputes filed by buyers.

Three languages are supported - English, isiZulu and Sepedi. The
switcher is in the navbar. All strings live in
`includes/translations.php`.

## Payment

The card form has real fields (number, cardholder, expiry, CVV) and
does Luhn checksum validation on both the client and the server side.
A transaction reference like `TXN-...` is generated on a successful
order and shown on the confirmation. But no real money moves - it's a
simulation. Doing a real PayFast / Stripe sandbox integration was on
the list but I cut it for time.

## Stack

- PHP 8
- MySQL 8 / MariaDB
- Apache (XAMPP locally, php:8.2-apache Docker image in production)
- Vanilla JavaScript, no frameworks
- One CSS file at `assets/css/style.css`

## Hosting

Live on Railway. The MySQL service runs alongside the web container.
Seller-uploaded product images go to a Railway Volume mounted at
`/var/www/html/assets/images/products` so they persist across
redeploys.

## Things I would still add (Phase 2)

- Real PayFast sandbox integration
- Phone number verification with SMS OTP
- Buyer to seller messaging
- Seller withdrawal flow
- Filling the last few hardcoded English strings into the Zulu and
  Sepedi translation arrays
