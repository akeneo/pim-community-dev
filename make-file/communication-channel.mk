communication-channel-lint-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --level=8 vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Application vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Domain
	$(PHP_RUN) vendor/bin/phpstan analyse --level=5 vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Infrastructure

communication-channel-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/.php_cd.php vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back
