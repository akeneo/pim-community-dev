DEV_CATALOG_DIR=src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo ""
	@echo "Caution: those targets are optimized for docker 19+"
	@echo ""
	@echo "Please add your custom Makefile in the directory "make-file". They will be automatically loaded!"
	@echo ""

include make-file/*.mk
