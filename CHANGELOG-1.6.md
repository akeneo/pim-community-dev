# 1.6.x

## Technical improvements

- PIM-5589: introduce a channels and attribute groups import using the new import system introduced in v1.4

## BC breaks

- Installer fixtures now support csv format for channels setup and not anymore the yml format
- Installer fixtures does not support anymore the yml format for association types
- Installer fixtures now support csv format for attribute groups setup and not anymore the yml format
- Remove deprecated argument $propertyCopier from constructor of `Pim\Component\Catalog\Updater\ProductUpdater` and allow to inject supported fields
