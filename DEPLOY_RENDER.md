# Guide de Déploiement sur Render

## 📋 Prérequis
- Un compte GitHub avec votre projet poussé
- Un compte [Render.com](https://render.com)
- Docker installé localement (pour tester)

## 🚀 Étapes de Déploiement

### 1. **Préparer votre Git**
```bash
cd c:\xampp\htdocs\B1-Ynov\Yboost\ProjetHebergement
git init
git add .
git commit -m "Initial commit: Docker setup for Render"
git remote add origin https://github.com/<votre-username>/<votre-repo>.git
git push -u origin main
```

### 2. **Créer un service Web sur Render**

#### Lier votre GitHub :
1. Allez sur [https://dashboard.render.com](https://dashboard.render.com)
2. Cliquez sur "New" → "Web Service"
3. Connectez votre repository GitHub
4. Sélectionnez votre repo

#### Configurer le service Web :
- **Name** : `messagerie-app` (ou votre choix)
- **Runtime** : Docker
- **Build Command** : `docker build -t myapp .`
- **Start Command** : (laissez vide, utilise le Dockerfile)

#### Variables d'environnement (onglet "Environment") :
```
DB_HOST=<votre-connexion-db.onrender.com>
DB_NAME=bdd_messagerie
DB_USER=messagerie_user
DB_PASS=<votre_mot_de_passe>
DB_PORT=5432
```

### 3. **Créer une Base de Données PostgreSQL sur Render**

⚠️ **Note** : Render recommande PostgreSQL au lieu de MySQL pour les plans gratuits.

#### Alternative : Utiliser Railway ou Clever Cloud pour MySQL

Si vous préférez rester avec MySQL :
1. [Railway.app](https://railway.app) offre MySQL gratuitement
2. Utilisez la chaîne de connexion fournie par Railway

### 4. **Tester Localement avec Docker Compose**

```bash
# Démarrer l'application localement
docker-compose up --build

# L'application sera accessible à http://localhost
# La base de données à localhost:3306
```

### 5. **Initialiser la Base de Données**

Une fois votre base créée sur Render :

```bash
# Exécuter le script SQL sur votre base Render
mysql -h <votre-host> -u <user> -p < bdd/bdd.sql
```

Ou connectez-vous via phpMyAdmin/Adminer si disponible.

## 📦 Structure des Fichiers Créés

- **Dockerfile** : Configuration pour construire l'image Docker
- **docker-compose.yml** : Orchestration locale (PHP + MySQL)
- **.dockerignore** : Fichiers à exclure du build Docker
- **.env.example** : Template des variables d'environnement
- **render.yaml** : Configuration optionnelle pour Render (CLI)

## 🔧 Modifications Effectuées

✅ **configBdd.inc.php** : Lecture des variables d'environnement
```php
$host = getenv('DB_HOST') ?: 'localhost';
$name = getenv('DB_NAME') ?: 'bdd_messagerie';
// etc...
```

✅ **fonctionConnexionBdd.inc.php** : Support du port personnalisé

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez les variables d'environnement sur Render
- Assurez-vous que l'IP du service Web est whitelistée dans la BD

### Uploads ne fonctionnent pas
- Render ne persiste que certains dossiers
- Solution : Utiliser Render Disks ou un service externe (S3, Supabase)

### Application très lente
- Plan gratuit Render = limitation des ressources
- Envisagez un plan payant pour la production

## 📝 Commandes Utiles

```bash
# Rebuild et redémarrer
docker-compose down
docker-compose up --build

# Vérifier les logs de Render
render logs -s <service-id>

# Exécuter une commande dans le conteneur
docker-compose exec web bash
```

## 🌐 Domaine Personnalisé

Dans les paramètres du service Render :
1. Onglet "Settings"
2. "Custom Domain"
3. Suivez les instructions pour le DNS

---

💡 **Besoin d'aide ?** Consultez la [documentation Render](https://render.com/docs)
