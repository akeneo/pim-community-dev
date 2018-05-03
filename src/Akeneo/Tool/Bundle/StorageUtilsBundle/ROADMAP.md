# 1.3
##  Storage
 - [x] extract base classes
 - [x] activate the bundle
 - [x] rename AkeneoDoctrineHybridSupportBundle to AkeneoDoctrineExtensionBundle
 - [x] alias former pim_catalog services to avoid BC breaks
 - [x] make behat pass 
 - [x] make sure all former pim_catalog services are not used anymore (CE and EE)
 - [x] make tests pass
 - [x] remove all text occurrences of product or catalog
 - [x] rename AkeneoDoctrineExtensionBundle to AkeneoStorageUtilsBundle (think about performing a string search)
 - [x] rename pim_catalog services to akeneo_storage_utils
 - [ ] continue to load pim_catalog_storage_driver if it exists and deprecate it 
 - [ ] test a migration from CE standard 1.2 to CE standard 1.3 to check that the parameter pim_catalog_storage_driver is still taken into account
 - [x] rename AbstractResolveDoctrineTargetModelsPass
 - [ ] prepare a PR for CE standard https://github.com/akeneo/pim-community-standard/pull/91
 - [x] prepare a PR for EE https://github.com/akeneo/pim-enterprise-dev/pull/481
 - [ ] prepare a PR for EE standard https://github.com/akeneo/pim-enterprise-standard/pull/28
 - [ ] add a note on UPGRADE.md
 - [ ] add all BC breaks to the changelog

## Repositories
 - [x] move work what @fitn did to ease the definition and override of repositories 
 - [x] be constistent between resolve_target_repository and resolve_target_repositories
 - [x] be constistent between private and public services
 - [x] rename ResolveDoctrineTargetRepositoriesPass

# LATER
##  Storage
 - [ ] add specs to existing classes
 - [ ] move specs into the bundle
 - [ ] documentation !
 - [ ] fix the mapping of the objects so that:
    - [ ] ORM entities should be mapped only for ORM
    - [ ] Mongo documents should be mapped only for Mongo
 - [ ] extract CacheClearer
 - [ ] extract ObjectIdResolver
 - [ ] move what's relevant into a `HybridStorage` directory
 - [ ] a missing orm.yml or mongob.yml file should not make the app crash
 - [ ] extract what can be extracted from AkeneoDoctrineExtensionsExtension et AkeneoDoctrineExtensionsBundle to ease integration (use of simple helper or something like that)
 - [ ] make storage_driver/doctrine/storage.yml file loaded for all bundles ? (not sure, maybe too magic)
 - [ ] clean the constant AkeneoDoctrineExtensionsExtension::DOCTRINE_MONGODB which is useless
 - [ ] define the configuration of the interfaces with regular Doctrine configuration ?
 - [ ] change the storage `doctrine-mongodb` by `hybrid` 
 
## Objects' mapping
 - [ ] add a listener similar to Sylius to switch mapped superclasses to regular objects (ease mapping overriding + get rid of useless empty abstract classes) 

## Extraction
 - [ ] target the extraction of this bundle for 1.4 or 1.5 depending on our needs and the community
