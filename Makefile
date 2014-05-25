REF = $(shell git rev-parse --short HEAD)
THEME_PATH = public/wp-content/themes/wp-starter-kit
DIR_STAGING = /var/www/wp-starter-kit
DIR_PRODUCTION = /var/www/wp-starter-kit
SSH_USER_STAGING = username-staging
SSH_HOST_STAGING = host-staging
SSH_PORT_STAGING = 22
SSH_USER_PRODUCTION = username-production
SSH_HOST_PRODUCTION = host-production
SSH_PORT_PRODUCTION = 22

.PHONY: init build clean rsync-staging rsync-production staging deploy pull watch

build:
	@mkdir .tmp
	@cp $(THEME_PATH)/header.php .tmp/header.php
	@cp $(THEME_PATH)/footer.php .tmp/footer.php
	@python usemin.py .tmp/header.php $(THEME_PATH)/header.php $(THEME_PATH)/ $(REF)
	@python usemin.py .tmp/footer.php $(THEME_PATH)/footer.php $(THEME_PATH)/ $(REF)

clean:
	@echo Restoring script and link refs...
	@-mv .tmp/footer.php $(THEME_PATH)/footer.php
	@-mv .tmp/header.php $(THEME_PATH)/header.php
	@echo Cleaning up files...
	@-rm -r .tmp
	@-rm $(THEME_PATH)/css/style-$(REF).css
	@-rm $(THEME_PATH)/js/modernizr-$(REF).js
	@-rm $(THEME_PATH)/js/main-$(REF).js

rsync-staging:
	@echo Deploying to STAGING server...
	@rsync -avz --progress --delete --exclude ".tmp" --exclude ".DS_Store" --exclude ".sass-cache" --exclude ".git" --exclude ".gitignore" --exclude="node_modules" -e "ssh -p $(SSH_PORT_STAGING)" ./ $(SSH_USER_STAGING)@$(SSH_HOST_STAGING):$(DIR_STAGING)

rsync-production:
	@echo Deploying to PRODUCTION server...
	@rsync -avz --progress --delete --exclude ".tmp" --exclude "public/wp-content/uploads" --exclude ".DS_Store" --exclude ".sass-cache" --exclude ".git" --exclude ".gitignore" --exclude="node_modules" -e "ssh -p $(SSH_PORT_PRODUCTION)" ./ $(SSH_USER_PRODUCTION)@$(SSH_HOST_PRODUCTION):$(DIR_PRODUCTION)

staging: build rsync-staging clean

deploy: build rsync-production clean

pull:
	@rsync -avz --progress -e "ssh -p $(SSH_PORT_PRODUCTION)" $(SSH_USER_PRODUCTION)@$(SSH_HOST_PRODUCTION):$(DIR_PRODUCTION)public/wp-content/uploads/ ./public/wp-content/uploads/

watch:
	@node_modules/.bin/node-sass --watch --source-map $(THEME_PATH)/css/style.css.map $(THEME_PATH)/scss/style.scss $(THEME_PATH)/css/style.css

init:
	@mkdir .tmp
	@curl http://wordpress.org/latest.zip > .tmp/wordpress.zip
	@unzip .tmp/wordpress.zip -d .tmp
	@cp -R .tmp/wordpress/* public
	@rm -r .tmp
	@cp wp-config-sample.php wp-config.php
	@npm install
	@bower install
