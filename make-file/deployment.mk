.PHONY: tests
tests:
	echo "make phpspec"
	echo "make acceptance"
	echo "make phpunit"
	echo "make behat-legacy"
	echo "make FRONT tests"
	echo "make PROD tests"
	echo "and more stuff to do here..."
	exit 1

.PHONY: release
release:
	exit 1
