# 1.8.*

## Functional improvements

- TIP-718: Update group types form

## Technical improvements

- TIP-711: Rework job execution reporting page with the new PEF architecture

## BC breaks

### Constructors

- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\JobTrackerController` to add `Oro\Bundle\SecurityBundle\SecurityFacade` and add an associative array 
- Change the constructor of `Pim\Component\Catalog\Updater\FamilyUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `kernel.logs_dir`
