Thank you to read and sign the following contributor agreement http://www.akeneo.com/contributor-license-agreement/

Every PR should start with:

```
| Q                    | A
| -------------------- | ---
| Bug fix?             |
| New feature?         |
| BC breaks?           |
| CI currently passes? |
| Tests pass?          |
| Scenarios pass?      |
| Checkstyle issues?*  |
| PMD issues?**        |
| Changelog updated?   |
| Fixed tickets        |
| Doc PR               |
```

*``` > ./bin/phpcs --standard=PSR2 --extensions=php src/Pim features```

**``` > ./app/Resources/jenkins/phpmd_akeneo src/Pim,features text``` (only on your added/updated files)
