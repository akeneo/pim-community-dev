.PHONY: tests
tests:
	echo "make coupling"
	echo "make phpspec"
	echo "make acceptance"
	echo "make phpunit"
	echo "make behat-legacy"
	echo "make FRONT tests"
	echo "make PROD tests"
	echo "and more stuff to do here..."

.PHONY: release
release:
	echo "push pim images"
	echo "tag"

.PHONY: deploy_pim
deploy_pim:
	echo "deploy SaaS customers"
	echo "migrate SaaS customers"
	echo "deploy ref envs customers"
	echo "a lot of stuff to see with Franck"

.PHONY: deploy_doc
deploy_doc:
	echo "deploy docs.akeneo.com"
	echo "deploy api.akeneo.com"
	echo "deploy helpcenter.akeneo.com"

.PHONY: notify
notify:
	echo "notify slack #user-group"
	echo "notify slack #product-news"
