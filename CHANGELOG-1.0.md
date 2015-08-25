# 1.0.30 (2015-08-18)
- Update community-edition dependency to 1.2.36 version.

# 1.0.29 (2015-07-06)
- Update community-edition dependency to 1.2.36 version.

# 1.0.28 (2015-05-29)

## Bug fixes
- PIM-4227: Disable product versionning on category update (never used and very slow)

# 1.0.27 (2015-05-27)

## Bug fixes
- PIM-4223: Fix grid sorting order initialization (changed to be consistent with Platform behavior)

# 1.0.26 (2015-03-16)

## Bug fixes
- PIM-3883: Mass publish does not work on Mongo
- PIM-3898: Quick export does not work on published products

# 1.0.25 (2015-03-11)
- Update community-edition dependency to 1.2.32 version.

# 1.0.24 (2015-03-06)
- Update community-edition dependency to 1.2.31 version.

## Bug fixes
- PIM-3658: Mass publish time are exponential and not linear

# 1.0.23 (2015-03-02)

## Bug fixes
- PIM-3837: Fix XSS vulnerability on user form

# 1.0.22 (2015-02-24)

## Bug fixes
- PIM-3808: Fix the export of published products

# 1.0.21 (2015-02-20)
- Update community-edition dependency to 1.2.28 version.

# 1.0.20 (2015-02-13)
- Update community-edition dependency to 1.2.27 version.

# 1.0.19 (2015-02-12)
- Update community-edition dependency to 1.2.26 version.

# 1.0.18 (2015-02-04)

## Bug fixes
- PIM-3717: Fix handling draft save with special attributes

# 1.0.17 (2015-01-28)

## Bug fixes
- PIM-3712: Fix installation issue related to the tag of gedmo/doctrine-extensions v2.3.11, we freeze to v2.3.10

# 1.0.16 (2015-01-23)

## Bug fixes
- PIM-3664: Fix product media stacktrace regression on missing media on filesystem during an export

# 1.0.15 (2015-01-21)

## Bug fixes
- PIM-3636: Fix the failing publish of associations when mass publish products

# 1.0.14 (2015-01-16)

## Bug fixes
- PIM-3615: Context of the grid not applied in product form for an attribute type date

# 1.0.13 (2015-01-14)

## Bug fixes
- PIM-3603: Trigger saving wysiwyg editor contents when submitting product form manually

# 1.0.12 (2015-01-14)

## Bug fixes
- PIM-3548: Do not rely on the absolute file path of a media

# 1.0.10 (2014-12-23)

## Bug fixes
- PIM-3533: Fix wrong keys being generated for empty price attributes in normalized product snapshots to fix the revert of product versions

# 1.0.9 (2014-12-17)

## Improvements
- PIM-3475: Add sort order in options attribute to sample catalog

## Bug fixes
- PIM-3448: Avoid to allow to approve/refuse/delete a proposal if the user can't edit all values of this proposal

# 1.0.8 (2014-12-10)

## Bug fixes
- PIM-3449: Fix performance problem on on grid with many categories

# 1.0.7 (2014-12-03)

## Bug fixes
- PIM-3444: Fix the enabled flag not published

# 1.0.6 (2014-11-26)

## Bug fixes
- PIM-3302: Published product page is not well displayed with project via the standard edition. To fix this bug, add `bundles/pimenterpriseui/css/pimee.less` to your stylesheets in `app/Resources/views/base.html.twig`
- PIM-3385: Fix deleting attributes not linked to published products

# 1.0.5 (2014-11-13)

## Bug fixes
- PIM-3331: Fix draft creation when saving values for newly added attributes for the first time
- PIM-3351: Test data value in PricesDenormalizer to avoid creating empty ProductPrices
- PIM-3301: Test data value in DateTimeDenormalizer to avoid reverted date to be set on current day
- PIM-3354: Fix category filter on multi-positioned product on unclassified

# 1.0.4 (2014-10-31)

## Bug fixes
- PIM-3300: Fixed bug on revert of a multi-select attribute options

# 1.0.3 (2014-10-24)

## Bug fixes
- PIM-3206: Removing the group "ALL" from categories' permissions after a clean installation.
- PIM-3234: Fix performance issue on granted category filter

# 1.0.2 (2014-10-10)

## Bug fixes
- Fix installer fail on requirements when you change the archive and uploads folder
- Fixed icecat-demo-dev fixtures
- Setup relationships of published products with interfaces in order to ease overriding
- Fixed problem on view rights on attribute groups which were not displayed on family configuration
- Fixed a bug with wysiwyg values disappearing from drafts when saved without modifications
- Fixes products count by tree performances problem with lots of categories
- Stabilize composer.json (minimum-stability: stable) and fix monolog version issue

## Improvements

## BC breaks

# 1.0.1 (2014-09-10)

## Bug fixes

## Improvements

## BC breaks

# 1.0.0 (2014-08-29)

## Improvements

## Bug fixes
- Fixed read only mode of products page
- Fixed a regression on product draft submission

## BC breaks

# 1.0.0-RC3 (2014-08-27)

## Improvements
- Java dependency has been removed
- Add fallback to en_US locale
- Application is partially translated in French

## Bug fixes
- Fixed disabled file input appearing as if it were enabled
- Fixed product draft validation
- Don't allow users without corresponding rights to edit entity permissions
- Imported categories now have the permissions of their parent

## BC breaks
- Change constructor of `PimEnterprise/Bundle/EnrichBundle/Form/Subscriber/AttributeGroupPermissionsSubscriber`, `PimEnterprise/Bundle/EnrichBundle/Form/Subscriber/CategoryPermissionsSubscriber` and `src/PimEnterprise/Bundle/EnrichBundle/Form/Subscriber/LocalePermissionsSubscriber` to inject `Oro\Bundle\SecurityBundle\SecurityFacade` as the second argument
- JS and CSS are not minified anymore. We advise to use server side compression for bandwidth savings.
- Change constructor of `PimEnterprise/Bundle/SecurityBundle/Manager/CategoryAccessManager` to add the user group class as fourth argument
- Methods `setAccess` of `PimEnterprise/Bundle/SecurityBundle/Manager/CategoryAccessManager` now has a last parameter to handle the flush

# 1.0.0-RC2 (2014-08-14)

## Improvements
- Apply permissions on REST API
- Change labels of permissions in category, locale, attribute group and import/export profiles
- Load locales in installer with default permissions
- Replace the grey background with a border for fields in product view
- New CSS for publish/unpublish/send for approval buttons
- Display completeness in the product and published product view page
- Add confirmation before publishing or unpublishing a product
- Implement automatic inherited permissions selection for all entities (javascript)
- Add missing page titles on product and published product view
- Add default user group "All" in permission when create a tree, locale, attribute group
- Don't allow users to classify products they don't own
- Fix product count in mass publish/unpublish operations

## Bug fixes
- Fix extraction of category ids from DatagridView filters to properly filter views the user can apply
- Fix the number of proposals displayed in the proposal widget
- Take into account permission on 'Edit working copy' button

## BC breaks
- Put media back into published product (as now in product)
