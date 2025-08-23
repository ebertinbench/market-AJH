reset-env-db:
	php market-ajh/bin/console doctrine:database:drop --force
	php market-ajh/bin/console doctrine:database:create
	php market-ajh/bin/console doctrine:schema:update --force
	php market-ajh/bin/console doctrine:fixtures:load --no-interaction

migration-no-check:
	php market-ajh/bin/console make:migration
	php market-ajh/bin/console doctrine:migrations:migrate


migration-check-1:
	php market-ajh/bin/console make:migration
	@echo "Veuillez vérifier les changements dans le fichier de migration créé."
	@echo "Une fois vos verifications effectuées, lancez 'make migration-check-2' pour appliquer les migrations."

migration-check-2: migration-check-1
	php market-ajh/bin/console doctrine:migrations:migrate

build-prod-webpack-encore:
	/opt/alt/alt-nodejs22/root/usr/bin/npm install --prefix market-ajh
	/opt/alt/alt-nodejs22/root/usr/bin/npm run build --prefix market-ajh