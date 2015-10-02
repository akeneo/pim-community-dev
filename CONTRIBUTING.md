Thank you to read and sign the following contributor agreement http://www.akeneo.com/contributor-license-agreement/

Every PR should start with:

```
| Q                  | A
| ------------------ | ---
| Migration script?  |
| Behats             |
| Specs & Static     |
| Changelog updated  |
```

*``` > ./bin/phpcs --standard=PSR2 --extensions=php src features && php-cs-fixer fix -v --diff --level=psr2 --config-file=.php_cs.local.php```

**``` > ./app/Resources/jenkins/phpmd_akeneo src/Pim,features``` (only on your added/updated files)
