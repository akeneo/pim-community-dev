Thank you to read and sign the following contributor agreement http://www.akeneo.com/contributor-license-agreement/

Every PR should start with:

```
Bug fix: [yes|no]
Feature addition: [yes|no]
Backwards compatibility break: [yes|no]
Unit test passes: [yes|no]
Behat scenarios passes: [yes|no]
Checkstyle issues: [yes|no]*
Documentation PR: [link to the documentation PR if there is one]
Fixes the following jira:
 - ...
```

PR with Platform upgrade:
```
App
[] Check configuration
[] Check routing

FilterBundle
[] Check filter changes between pim-layout.js.twig and layout.js.twig

GridBundle
[] Update differences between grid.js and pim-grid.js
[] Update differences between javascript.html.twig and pim-datagrid.html.twig
```

*Use http://cs.sensiolabs.org/
