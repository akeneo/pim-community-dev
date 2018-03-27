# 2.1.8 (2018-03-27)

## Bug fixes

- PIM-7213: Improve performances with a big amounts of associations

# 2.1.7 (2018-03-20)

# 2.1.6 (2018-03-13)

## Web API

- API-580: Manage permissions about associations between products and product models

# 2.1.5 (2018-02-23)

# 2.1.4 (2018-02-01)

# 2.1.3 (2018-02-01)

# 2.1.2 (2018-01-23)

## Bug fixes

- PIM-7088: Fix remove association with a product model
- API-582: fix "localizable" and "scope" field of the asset format in the API

## Web API

- API-557: Ensure assets can be used as attribute as main picture in families

# 2.1.1 (2018-01-10)

## Bug fixes

- PIM-7066: Fix permissions problems on required missing attributes

# 2.1.0 (2017-12-21)

## Web API

- API-429: Create an asset with the API
- API-430: Update partially an asset with the API
- API-431: Update partially a list of assets with the API
- API-487: Upload a reference file with the API
- API-493: Upload a variation file with the API

## BC breaks

- Add method `retrieveVariationsNotGeneratedForAReference` to the interface `PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface`

# 2.1.0-ALPHA2 (2017-12-15)

## Web API

- API-529: Get an asset tag
- API-530: Get a list of asset tags
- API-531: Upsert a single asset tag
- API-440: Create an asset category with the API
- API-441: Update partially an asset category with the API
- API-442: Update partially a list of asset categories with the API
- API-488: Get an asset reference file with the API
- API-496: Get an asset variation file with the API
- API-514: Download an asset reference file with the API
- API-513: Download an asset variation file with the API
- API-427: Get an asset with the API
- API-428: Get a list of assets with the API
- API-443: Prevent getting asset via media file url with the API

## Have a better UX/UI

- PIM-7029: Add a search by code on assets grid
- PIM-7024: Add search on label and identifier on published products

## Bug fixes

- TIP-827: Change caching strategy of the authorization checker

# 2.1.0-ALPHA1

## Web API

- API-438: Get an asset category with the API
- API-439: Get a list of asset categories with the API

## Have a better UX/UI

- PIM-6946: Allow assets to be attribute as main picture for families
- PIM-6481: Add gallery view and display selector to assets grid
