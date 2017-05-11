# 1.8.*

## Functional improvements

- TIP-718: Update group types form

## Technical improvements

- TIP-711: Rework job execution reporting page with the new PEF architecture
- TIP-724: Refactoring of the 'Settings/Association types' index screen using 'pim/common/grid'
- TIP-725: Generalization of the refactoring made in the TIP-724 for all screen containing a simple grid 

## BC breaks

### Constructors

- Change the constructor of `Pim\Bundle\EnrichBundle\Controller\JobTrackerController` to add `Oro\Bundle\SecurityBundle\SecurityFacade` and add an associative array 
- Change the constructor of `Pim\Component\Catalog\Updater\FamilyUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Pim\Component\Catalog\Updater\AttributeUpdater` to add `Akeneo\Component\Localization\TranslatableUpdater`
- Change the constructor of `Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher` to add `kernel.logs_dir`
- Change the constructor of `Pim\Bundle\EnrichBundle\Twig\AttributeExtension` to remove `pim_enrich.attribute_icons`

## Requirements

- GITHUB-5937: Remove the need to have mcrypt installed

## Bug Fixes

- GITHUB-6101: Fix Summernote (WYSIWYG) style
