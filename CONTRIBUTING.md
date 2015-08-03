Every PR should start with:

```
| Q                  | A
| ------------------ | ---
| Migration script?  |
| Behats             |
| Specs & Static     |
| Changelog updated  |
| Related CE PR      |
```

*``` > ./bin/phpcs --standard=PSR2 --extensions=php src/PimEnterprise features && php-cs-fixer fix -v --diff --config-file=.php_cs && php-cs-fixer fix -v --diff --config-file=.php_cs_spec```

**``` > ./app/Resources/jenkins/phpmd_akeneo src/PimEnterprise,features``` (only on your added/updated files)
