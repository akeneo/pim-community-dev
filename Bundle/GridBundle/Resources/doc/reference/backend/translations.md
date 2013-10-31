Translations
------------

OroGridBundle provides translation mechanism which can translate localized information (column and row action labels).

DatagridManager contains translator object and translation domain and provides method _translate_
which use translator object to perform translation. Default translation domain is "datagrid".

#### Configuration

**Translator and translation domain configuration**

Datagrid manager can customize translator service and translation domain.

```
services:
    orocrm_contact.contact.datagrid_manager:
        class: %orocrm_contact.contact.datagrid_manager.class%
        tags:
            - name: oro_grid.datagrid.manager
              ...
              translator: acme_demobundle.custom_translator
              translation_domain: acme_datagrid
```

**Default translation domain configuration**

Default translation domain can be customized in main configuration.

```
oro_grid:
    translation_domain: acme_datagrid
```

Another way to customize default translation domain is to set it as parameter in some specific bundle configuration.

```
parameters:
    oro_grid.translation.translation_domain: acme_datagrid
```
