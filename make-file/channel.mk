##
## Target used run command related on Channel Bounded context
##

.PHONY: channel-coupling
channel-coupling:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Channel/.php_cd.php src/Akeneo/Channel
