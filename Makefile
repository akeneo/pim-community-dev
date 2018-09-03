ENV=prod

clear-cache: 
	rm -rf ./var/cache
	
pim-front: clear-cache
	rm -rf ./web/js ./web/css
	bin/console pim:installer:assets --env=$(ENV) --symlink --clean
	yarn run webpack-dev

install: clear-cache
	bin/console console pim:install --env=$(ENV) --force

