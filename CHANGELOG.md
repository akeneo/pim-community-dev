# 1.0.0-x
## Improvements
## Bug fixes
## BC breaks

# 1.0.0

## Improvements

## Bug fixes
- Fixed read only mode of products page
- Fixed a regression on product draft submission

## BC breaks

# 1.0.0-RC3

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

# 1.0.0-RC2

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
