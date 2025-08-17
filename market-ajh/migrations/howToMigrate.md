# 1. Check des dépendances
```
composer require symfony/orm-pack
composer require doctrine/doctrine-migrations-bundle
composer require symfony/maker-bundle --dev
```

# 2. Génération de la migration
```
php bin/console make:migration
```

# 3. Vérification du code SQL qui va être éxecuté
Il faut notamment prêter attention:
- Aux valeurs par défaut
- Aux contraintres NULL / NOT NULL
- Aux contraintes FOREIGN KEY

# 4. Execution de la migration
```
php bin/console doctrine:migrations:migrate
```