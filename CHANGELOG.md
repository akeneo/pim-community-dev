# 1.0.0-beta-2 - "Hold the Lion, Please" (2013-10-29)

## Features
- Manage variant groups
- CRUD actions on groups
- Manage association between groups and products
- CRUD actions on association entities
- Link products with associations
- Import medias from a CSV file containing name of files
- Export medias from a CSV file
- Apply rights on locales for users
- Do mass classification of products
- Define price attribute type with localizable property

## Improvements
- Upgrade to BAP Beta 1
- Homogenize title/label/name entity properties using label
- Mass actions respects ACL
- Improve Import/Export profile view
- Hide access to shortcut to everyone
- Number, date and datetime attributes can be defined as unique values
- Use server timezone instead of UTC timezone for datagrids
- Make upload widget work on FireFox
- Display skipped data errors on job report

## Bug fixes
- Fix sorting channels by categories
- Bug #324 : Translate group label, attribute label and values on locale switching
- Number of products in categories are not updated after deleting products
- Fix dashboard link to create import/export profile
- Fix price format different between import and enrich
- Fix channel datagrid result count
- Fix end date which is updated for all jobs

