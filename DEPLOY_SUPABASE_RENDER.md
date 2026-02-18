# 🚀 Guide de Déploiement sur Render + Supabase

## 📋 Prérequis
- Compte [Supabase](https://supabase.com) (gratuit)
- Compte [Render.com](https://render.com)
- Projet sur GitHub

---

## 🔧 **Étape 1 : Configurer Supabase**

### Créer une base de données sur Supabase
1. Allez sur [https://supabase.com](https://supabase.com)
2. Créez un nouveau projet
3. Une fois créé, allez dans **Settings > Database**
4. Copiez les informations de connexion :
   - **Host** : `xxxxx.supabase.co`
   - **Database** : `postgres`
   - **User** : `postgres`
   - **Password** : (générée)
   - **Port** : `5432`

### Initialiser le SQL sur Supabase
1. Dans Supabase, allez dans l'onglet **SQL Editor**
2. Créez une nouvelle requête
3. Collez le contenu de [bdd_postgresql.sql](bdd/bdd_postgresql.sql)
4. Exécutez la requête ✅

### Alternative : Utiliser psql en ligne de commande
```bash
# Remplacez les valeurs par celles de Supabase
psql -h xxxxx.supabase.co -U postgres -d postgres -f bdd/bdd_postgresql.sql
```

---

## 🌐 **Étape 2 : Déployer sur Render**

### Pousser le code sur GitHub
```powershell
cd c:\xampp\htdocs\B1-Ynov\Yboost\ProjetHebergement

git init
git add .
git commit -m "Setup Docker + Supabase"
git remote add origin https://github.com/<votre-username>/<votre-repo>.git
git branch -M main
git push -u origin main
```

### Créer un Web Service sur Render
1. Allez sur [https://dashboard.render.com](https://dashboard.render.com)
2. Cliquez sur **New** → **Web Service**
3. Connectez votre repository GitHub
4. Configurez :
   - **Name** : `messagerie-app`
   - **Runtime** : `Docker`
   - **Build Command** : (laissez vide, utilise le Dockerfile)
   - **Start Command** : (laissez vide)

### ⚙️ Ajouter les variables d'environnement
Dans les paramètres du service (onglet **Environment**), ajoutez :

```
DB_TYPE=pgsql
DB_HOST=xxxxx.supabase.co
DB_NAME=postgres
DB_USER=postgres
DB_PASS=<votre_mot_de_passe_supabase>
DB_PORT=5432
APP_ENV=production
```

---

## 🔄 **Alternative : Utiliser la migration directement**

Si vous voulez éviter d'exécuter le SQL manuellement, vous pouvez :

1. Créer un script d'initialisation dans le Dockerfile :

```dockerfile
# Dans le Dockerfile, avant CMD
RUN which psql > /dev/null 2>&1 || apt-get update && apt-get install -y postgresql-client

COPY init-supabase.sh /tmp/
RUN chmod +x /tmp/init-supabase.sh
```

2. Créer `init-supabase.sh` :

```bash
#!/bin/bash
if [ "$DB_TYPE" = "pgsql" ]; then
    psql -h $DB_HOST -U $DB_USER -d $DB_NAME -f /var/www/html/bdd/bdd_postgresql.sql
fi
```

---

## 🧪 **Tester localement avec Docker (optionnel)**

Si tu veux tester localement avec PostgreSQL :

```bash
# Créer un conteneur PostgreSQL temporaire
docker run --name postgres-test -e POSTGRES_PASSWORD=password -d postgres:15

# Exécuter le script SQL
docker exec -i postgres-test psql -U postgres -d postgres < bdd/bdd_postgresql.sql

# Nettoyer
docker stop postgres-test && docker rm postgres-test
```

---

## 🌍 Configuration finale

Une fois déployé, votre application est accessible à :
```
https://messagerie-app.onrender.com
```

---

## ✅ Checklist de déploiement

- [ ] Base Supabase créée
- [ ] Script SQL PostgreSQL exécuté
- [ ] Repo GitHub pushé
- [ ] Web Service créé sur Render
- [ ] Variables d'environnement configurées
- [ ] URL Render accessible

---

## 🐛 Dépannage

### Erreur "Cannot connect to database"
- Vérifiez que l'IP de Render est whitelistée dans Supabase (Settings > Network)
- Vérifiez les identifiants (DB_USER, DB_PASS, DB_HOST)

### Erreur "Table does not exist"
- Vérifiez que le script SQL a bien été exécuté sur Supabase
- Allez dans l'onglet **Table Editor** de Supabase pour vérifier les tables

### Uploads ne fonctionnent pas
- Utilisez [Supabase Storage](https://supabase.com/docs/guides/storage) pour les fichiers
- Ou utilisez un bucket S3 externe

---

## 📚 Ressources
- [Supabase Docs](https://supabase.com/docs)
- [Render Docs](https://render.com/docs)
- [PostgreSQL avec PHP PDO](https://www.php.net/manual/fr/ref.pdo-pgsql.php)
