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

maintenance:
	@if grep -q "MAINTENANCE_MODE=1" market-ajh/.env.local; then \
		sed -i 's/MAINTENANCE_MODE=1/MAINTENANCE_MODE=0/' market-ajh/.env.local; \
		echo "Mode maintenance désactivé"; \
	elif grep -q "MAINTENANCE_MODE=0" market-ajh/.env.local; then \
		sed -i 's/MAINTENANCE_MODE=0/MAINTENANCE_MODE=1/' market-ajh/.env.local; \
		echo "Mode maintenance activé"; \
	else \
		echo "la variable MAINTENANCE_MODE n'a pas été trouvée dans le fichier .env.local"; \
	fi

build:
	php market-ajh/bin/console cache:clear --env=prod
	/opt/alt/alt-nodejs22/root/usr/bin/npm install --prefix market-ajh
	/opt/alt/alt-nodejs22/root/usr/bin/npm run build --prefix market-ajh
	php market-ajh/bin/console cache:warmup --env=prod

build-local:
	php market-ajh/bin/console cache:clear --env=prod
	npm install --prefix market-ajh
	npm run build --prefix market-ajh
	php market-ajh/bin/console cache:warmup --env=prod


cache:
	php market-ajh/bin/console cache:clear --env=prod
	php market-ajh/bin/console cache:warmup --env=prod