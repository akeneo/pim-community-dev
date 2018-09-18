@javascript
Feature: Mass uploads assets and add them to asset collection
  In order to create product assets
  As a product manager
  I need to be able to upload multiple assets and add them to products or product models

  Background:
    Given the "default" catalog configuration
    And the asset temporary file storage has been cleared
    And the following attributes:
      | code             | label-en_US      | type                     | group | reference_data_name |
      | color            | Color            | pim_catalog_simpleselect | other |                     |
      | asset_collection | Asset collection | pim_assets_collection    | other | assets              |
    And the following "color" attribute options: red, yellow, black and white
    And the following family:
      | code    | requirements-ecommerce | requirements-mobile | attributes                 |
      | jackets | sku                    | sku                 | asset_collection,color,sku |

  @critical
  Scenario: Mass upload assets from product edit form
    Given the following product:
      | sku          | categories | family  |
      | basic_jacket | default    | jackets |
    And I am logged in as "Julia"
    When I am on the "basic_jacket" product page
    And I open the mass uploader of the asset collection
    And I select the assets to upload:
      | name                  |
      | akeneo.jpg            |
      | akeneo2.jpg           |
      | logo_akeneo-fr_FR.jpg |
    And I start assets mass upload
    And I import assets mass upload
    And I wait for the "apply_assets_mass_upload_into_asset_collection" job to finish
    Then the product "basic_jacket" should have the following values:
      | asset_collection | [akeneo], [akeneo2], [logo_akeneo] |

  Scenario: Mass upload assets from product model edit form
    Given the following family variants:
      | code           | family  | variant-axes_1 | variant-attributes_1 |
      | jacket_unisize | jackets | color          | color,sku            |
    And the following root product models:
      | code       | categories | family_variant |
      | jacket_uni | default    | jacket_unisize |
    And I am logged in as "Julia"
    When I am on the "jacket_uni" product model page
    And I open the mass uploader of the asset collection
    And I select the assets to upload:
      | name                  |
      | akeneo.jpg            |
      | akeneo2.jpg           |
      | logo_akeneo-fr_FR.jpg |
    And I start assets mass upload
    And I import assets mass upload
    And I wait for the "apply_assets_mass_upload_into_asset_collection" job to finish
    Then there should be the following product model:
      | code       | family_variant | asset_collection             |
      | jacket_uni | jacket_unisize | [akeneo], [akeneo2], [logo_akeneo] |
