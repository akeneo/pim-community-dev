# 1.0.0-x

## Improvements

## Bug fixes
- Fixed disabled file input appearing as if it were enabled
- PIM-2975: validation fails when a draft is submitted

## BC breaks

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
