# 1.6.x

## Technical improvements

- PIM-5589: introduce a channels import using the new import system introduced in v1.4 

## BC breaks

- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- AttributeGroupManager now takes AttributeGroupAccessRepository $repository, BulkSaverInterface $saver, $attGroupAccessClass as constructor arguments
- LocaleManager now takes LocaleAccessRepository $repository, BulkSaverInterface $saver, $localeClass as constructor arguments
