<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'BNGRC' ?> — BNGRC</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/bootstrap/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
</head>
<body>

<div class="app-wrapper">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="bi bi-shield-check"></i></div>
            <span class="brand-text">BNGRC</span>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Principal</div>
            <a href="<?= BASE_URL ?>/" class="nav-link <?= ($active_menu ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i>
                <span class="nav-label">Tableau de bord</span>
            </a>

            <div class="nav-section-title">Gestion</div>
            <a href="<?= BASE_URL ?>/besoins" class="nav-link <?= ($active_menu ?? '') === 'besoins' ? 'active' : '' ?>">
                <i class="bi bi-clipboard-check"></i>
                <span class="nav-label">Besoins</span>
            </a>
            <a href="<?= BASE_URL ?>/dons" class="nav-link <?= ($active_menu ?? '') === 'dons' ? 'active' : '' ?>">
                <i class="bi bi-gift-fill"></i>
                <span class="nav-label">Dons</span>
            </a>
            <a href="<?= BASE_URL ?>/caisse" class="nav-link <?= ($active_menu ?? '') === 'caisse' ? 'active' : '' ?>">
                <i class="bi bi-cash-coin"></i>
                <span class="nav-label">Caisse</span>
            </a>
        </nav>

        <button class="sidebar-toggle" id="sidebar-toggle">
            <i class="bi bi-chevron-left"></i>
        </button>
    </aside>

    <div id="sidebar-overlay" class="sidebar-overlay"></div>

    <div class="main-wrapper">

        <header class="topbar">
            <div class="topbar-left">
                <button class="mobile-menu-btn" id="mobile-menu-btn">
                    <i class="bi bi-list"></i>
                </button>
                <div class="topbar-brand">
                    <i class="bi bi-shield-check"></i>
                    <span>BNGRC — Suivi des dons</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-date">
                    <i class="bi bi-calendar3"></i>
                    <span><?= date('d/m/Y') ?></span>
                </div>
                <div class="user-badge">
                    <div class="user-avatar">AD</div>
                    <div class="user-info">
                        <div class="user-name">Administrateur</div>
                        <div class="user-role">Coordinateur</div>
                    </div>
                </div>
            </div>
        </header>

        <main class="content-area">
