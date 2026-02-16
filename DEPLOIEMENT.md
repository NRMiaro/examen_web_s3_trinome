# Guide de Déploiement - BNGRC Gestion des Dons

## 📋 Prérequis Serveur

- PHP 8.0 ou supérieur
- MySQL 5.7 ou supérieur
- Apache avec mod_rewrite activé
- Extensions PHP requises : `pdo`, `pdo_mysql`, `mbstring`

## 🚀 Installation sur le Serveur

### 1. Upload des Fichiers

Téléversez tous les fichiers du projet sur votre serveur via FTP/SFTP dans le répertoire de votre choix (ex: `/home/ETU004303/exam_trinome/`)

### 2. Configuration de la Base de Données

**a) Créer la base de données**

Connectez-vous à phpMyAdmin ou via MySQL CLI et importez le fichier SQL:

```bash
mysql -u VOTRE_USER -p
```

Puis exécutez:
```sql
source /chemin/vers/sql/16_02_2026_01.sql
```

Ou via phpMyAdmin: Importer > Choisir le fichier `sql/16_02_2026_01.sql`

**b) Configurer les identifiants**

Éditez le fichier `app/config/config.php` et mettez à jour les credentials:

```php
'database' => [
    'host'     => 'VOTRE_HOST',      // Ex: localhost ou 172.16.3.8
    'dbname'   => 'VOTRE_DB',        // Ex: db_s2_ETU004303
    'user'     => 'VOTRE_USER',      // Ex: ETU004303
    'password' => 'VOTRE_PASSWORD',  // Ex: kWxsDOQR
],
```

### 3. Configuration Apache

Le fichier `.htaccess` est déjà configuré dans `public/.htaccess`

**Important:** Vérifiez que la directive `RewriteBase` correspond à votre chemin:

```apache
RewriteBase /ETU004303/exam_trinome/
```

Si votre projet est à la racine du domaine:
```apache
RewriteBase /
```

### 4. Permissions des Fichiers

Assurez-vous que les permissions sont correctes:

```bash
# Donnez les bonnes permissions aux dossiers
chmod 755 app/
chmod 755 public/
chmod 755 vendor/

# Le dossier log doit être accessible en écriture
mkdir -p app/log
chmod 777 app/log
```

### 5. Configuration du Document Root

**Si vous contrôlez Apache:**
Configurez le Document Root pour pointer vers le dossier `public/`:

```apache
<VirtualHost *:80>
    DocumentRoot /home/ETU004303/exam_trinome/public
    <Directory /home/ETU004303/exam_trinome/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Si vous êtes en sous-dossier:**
Le `.htaccess` à la racine redirigera automatiquement vers `public/`

## 🔐 Sécurité en Production

### 1. Désactiver le Debugger

Éditez `app/config/services.php`:

```php
// Commenter cette ligne en production:
// Debugger::enable(); 

// Ou forcer le mode production:
Debugger::enable(Debugger::Production);
```

### 2. Activer HTTPS (Recommandé)

Dans `public/.htaccess`, décommentez:

```apache
# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Masquer les erreurs PHP

Dans `app/config/config.php`:

```php
// En production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', '0');
```

## ✅ Vérification de l'Installation

### Test 1: Accès à l'application
Visitez: `http://votre-domaine/ETU004303/exam_trinome/` (ou votre URL)

Vous devriez voir le tableau de bord.

### Test 2: Connexion à la DB
Vérifiez que vous voyez les données:
- `/besoins` - Liste des besoins (Riz, Huile)
- `/dons` - Liste des dons

### Test 3: CRUD
- Créez un nouveau besoin
- Modifiez-le
- Supprimez-le

## 🗂️ Structure Importante

```
exam_trinome/
├── app/
│   ├── config/
│   │   ├── config.php           # ⚠️ Configuration DB (à modifier)
│   │   ├── services.php         # ⚠️ Debugger (à désactiver en prod)
│   │   └── routes.php           # Routes de l'application
│   ├── controllers/             # Contrôleurs MVC
│   ├── models/                  # Modèles de données
│   ├── views/                   # Vues PHP
│   └── middlewares/             # Middlewares (CSP, etc.)
├── public/                      # 🌐 Document root
│   ├── index.php                # Point d'entrée
│   ├── .htaccess                # ⚠️ Configuration Apache
│   └── assets/                  # CSS, JS, Images
└── sql/
    └── 16_02_2026_01.sql        # Script d'installation DB
```

## 🐛 Dépannage

### Erreur 500
- Vérifiez les logs Apache: `/var/log/apache2/error.log`
- Vérifiez les logs Flight: `app/log/`
- Vérifiez les permissions des dossiers

### Page blanche
- Activez temporairement `display_errors` dans `config.php`
- Vérifiez que mod_rewrite est activé: `a2enmod rewrite`

### CSS ne charge pas
- Vérifiez que `RewriteBase` est correct dans `.htaccess`
- Vérifiez les permissions de `public/assets/`

### Erreur de connexion DB
- Vérifiez les credentials dans `config.php`
- Testez la connexion MySQL: `mysql -h HOST -u USER -p`
- Vérifiez que la DB existe: `SHOW DATABASES;`

## 📞 Support

Pour toute question, vérifiez:
1. Les logs dans `app/log/`
2. La documentation FlightPHP: https://docs.flightphp.com
3. Le fichier README.md du projet

## 🎯 URLs de l'Application

- **Dashboard:** `/`
- **Besoins:** `/besoins`
  - Nouveau: `/besoins/nouveau`
  - Modifier: `/besoins/{id}/modifier`
  - Supprimer: POST `/besoins/{id}/supprimer`
- **Dons:** `/dons`
  - Nouveau: `/dons/nouveau`
  - Modifier: `/dons/{id}/modifier`
  - Supprimer: POST `/dons/{id}/supprimer`

---

✨ **Application prête pour la production !**
