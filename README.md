# Tapestry of Time — Microservice Mailer

Microservice Symfony dédié à l'envoi d'emails transactionnels. Il est appelé par le backend principal (`wr602d_back`) lors de l'inscription d'un nouvel utilisateur. La communication est protégée par une clé API dans les headers HTTP.

## Ce que fait ce projet

Un seul endpoint : `POST /send-mail`. Il reçoit un JSON avec les champs `to`, `subject`, `message`, et optionnellement `fromName`, puis envoie l'email via SMTP (Mailhog en dev).

L'endpoint est protégé par le header `X-MAILER-API-KEY`. Sans la bonne clé, la requête est rejetée avec un 401.

## Prérequis

- PHP >= 8.4
- Composer
- Docker Desktop (pour Mailhog/Mailpit)
- Symfony CLI

## Installation et lancement

### 1. Installer les dépendances

```bash
cd microservice
composer install
```

### 2. Créer le fichier `.env.local`

```dotenv
APP_SECRET=une_chaine_aleatoire
MAILER_DSN=smtp://localhost:1025
MAILER_NO_REPLY_EMAIL=noreply@tapestry-of-time.fr
MAILER_REPLY_EMAIL=support@tapestry-of-time.fr
MAILER_FROM_NAME="Tapestry of Time"
API_HEADER_NAME=X-MAILER-API-KEY
API_HEADER_VALUE=tapestry_mailer_secret_key_2026
```

> La valeur de `API_HEADER_VALUE` doit correspondre à `MAILER_API_KEY` dans le `.env.local` du backend.

### 3. Démarrer Mailhog (pour recevoir les emails en dev)

Le backend principal (`wr602d_back`) embarque déjà Mailhog dans son `docker compose`. Si ce service est lancé, le microservice peut l'utiliser directement sur le port 1025.

Sinon, lancer un Mailhog indépendant :
```bash
docker run -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

### 4. Lancer le serveur

```bash
symfony server:start --port=8001
# ou
php -S localhost:8001 -t public/
```

Le microservice est accessible sur **http://localhost:8001**.

> L'URL `http://localhost:8001` doit correspondre à `MAILER_MICROSERVICE_URL` dans le `.env.local` du backend.

## Utilisation de l'API

```http
POST http://localhost:8001/send-mail
Content-Type: application/json
X-MAILER-API-KEY: tapestry_mailer_secret_key_2026

{
  "to": "utilisateur@example.com",
  "subject": "Bienvenue sur Tapestry of Time",
  "message": "Merci de vous être inscrit !",
  "fromName": "Tapestry of Time"
}
```

**Réponse succès :**
```json
{ "message": "Mail sent!", "status": 200 }
```

**Champs requis :** `to`, `subject`, `message`  
**Champ optionnel :** `fromName`

## Structure du projet

```
microservice/
├── src/
│   ├── Controller/
│   │   └── MailerController.php   # Endpoint POST /send-mail
│   ├── Security/
│   │   └── ApiAuthenticator.php   # Vérification du header X-MAILER-API-KEY
│   └── Service/
│       ├── MailerService.php      # Envoi de l'email via Symfony Mailer
│       └── Utils/
│           └── RequestChecker.php # Validation des champs requis
├── config/packages/
│   └── security.yaml              # Firewall stateless avec authenticator custom
└── composer.json
```

## Qualité du code

PHPStan (niveau 5) et PHP CS Fixer (@Symfony) sont configurés :

```bash
composer quality
```

0 erreur PHPStan, 0 violation CS Fixer.

Pour les lancer séparément :

```bash
composer phpstan
composer cs-check
composer cs-fix 
```
