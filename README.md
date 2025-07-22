# market-AJH
le market de la guilde Ah-Jin SAO France

# Présentation
**Bienvenue sur la page github du market de la guilde Ah-Jin.** \
 Ce dernier est complètement libre d'utilisation (dans le cadre le la license mise en place). Vous trouverez dans ce readme des instructions pour son installation, son déploiement mais aussi son utilisation, en tant qu'utilisateur lambda ou bien en tant qu'adminitrateur de votre machine.

Bien sûr ! Voici comment je te propose de **compléter ton `README.md`** pour qu'il soit clair et utilisable par n'importe qui souhaitant lancer le projet Symfony + Docker en local :

---

# Installation et utilisation du projet Symfony + Docker

## Ubuntu / Debian

### Prérequis

Certains scripts peuvent générer des boîtes de dialogue expliquant certaines manipulations situationnelles. Merci d'y prêter attention.

#### Installer Docker (mode rootless)

```bash
curl -fsSL https://get.docker.com/rootless | sh
```

Après l'installation, **suivez les instructions affichées** pour configurer votre session sans root (ajout des variables d'environnement notamment).

#### Installer PHP / Composer / Symfony CLI

```bash
sudo apt update
sudo apt install php php-xml php-cli php-mbstring unzip curl
sudo apt install composer
curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash
sudo apt install symfony-cli
sudo apt install php-mysql
```

Ajoutez Symfony CLI à votre PATH si nécessaire :

```bash
export PATH="$HOME/.symfony/bin:$PATH"
```

#### Installer Docker Compose (si nécessaire)

```bash
sudo apt install docker-compose
```
> Ou utilisez `docker compose` si vous avez une version récente de Docker intégrant Compose nativement.

---


## Clonage du projet

Clonez ce dépôt sur votre machine :

```bash
git clone https://github.com/votre-utilisateur/votre-repo.git
cd votre-repo
```

---

## Configuration du projet Symfony

1. **Installer les dépendances PHP :**

```bash
composer install
```

2. **Créer le fichier `.env.local` si besoin :**

Copiez le fichier `.env` en `.env.local` et adaptez les paramètres selon votre environnement.

```bash
cp .env .env.local
```

3. **Générer la clé d'application Symfony :**

```bash
php bin/console secrets:generate-keys
```

---

## Lancer les conteneurs Docker

Assurez-vous d'être dans le dossier du projet où se trouve le `docker-compose.yaml`, puis :

```bash
docker-compose up -d
```
ou avec la syntaxe moderne :
```bash
docker compose up -d
```

Cela va lancer tous les services définis (ex: base de données, serveur web, etc.).

---

## Initialiser la base de données (si nécessaire)

Si votre projet utilise une base de données et qu'un container `db` est configuré :

1. **Attendre que le container de la base de données soit prêt** (quelques secondes).
   
2. **Lancer les migrations :**

```bash
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate
```
ou si vous utilisez des fixtures :
```bash
php bin/console doctrine:fixtures:load
```

---

## Accéder à l'application

- Depuis votre navigateur : `http://localhost:8000` (ou le port que vous aurez configuré dans `docker-compose.yaml`).
- Symfony CLI peut aussi ouvrir automatiquement un serveur local via :

```bash
symfony server:start
```

(si vous ne voulez pas passer par Docker pour l'application elle-même).

---

# Commandes utiles

- **Arrêter les conteneurs** :

```bash
docker compose down
```

- **Rebuilder les conteneurs** (après des changements sur les Dockerfile) :

```bash
docker compose up --build -d
```

- **Voir les logs** :

```bash
docker compose logs -f
```

---

## Notes

- En cas de problème de permissions sur Linux, vous pouvez devoir ajouter votre utilisateur au groupe `docker` :

```bash
sudo usermod -aG docker $USER
newgrp docker
```

- Pensez à vérifier que vos ports (ex: 8000 pour Symfony, 3306 pour MySQL) ne sont pas utilisés par d'autres services.

---
# Vérification des dépendances et mise en place de la base de données
```bash
php bin/console doctrine:schema:update --force
```

# build tailwind
```bash
php bin/console tailwind:build
```

# Features 
- Chaque guilde doit pouvoir vendre des items
- historique de prix 
- location d'étagère (le droit aux autres guildes d'utiliser le site pour vendre) en échange d'un pourcentage du chiffre d'affaires calculé par SAGE. Le pourcentage est entre 15% et 20% et sera décidé en fonction de l'importance de la guilde en question.
