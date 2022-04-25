@restore-product-feature-enabled
Feature: Revert a variant product to a previous version
  In order to manage versioning for variant products
  As a product manager
  I need to be able to revert a variant product to a previous version

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code  | label-en_US | type                     | localizable | scopable | group | decimals_allowed | unique |
      | color | Color       | pim_catalog_simpleselect | 0           | 0        | other |                  | 0      |
      | size  | Size        | pim_catalog_simpleselect | 0           | 0        | other |                  | 0      |
      | ean   | EAN         | pim_catalog_text         | 0           | 0        | other |                  | 1      |
    And the following "color" attribute options: blue, green, yellow, black and white
    And the following "size" attribute options: s, m, l, xl and xxl
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes         |
      | bags | sku                    | sku                 | color,ean,size,sku |
    And the following family variants:
      | code         | family | variant-axes_1 | variant-attributes_1 |
      | bags_variant | bags   | color          | color,ean,size,sku   |
    And the following root product models:
      | code  | family_variant |
      | james | bags_variant   |
      | rita  | bags_variant   |
    And the following product:
      | sku        | parent | family | color  | ean           | size |
      | bag_yellow | james  | bags   | yellow | 1234567890131 | xl   |

  @javascript
  Scenario: Successfully revert a variant product
    Given I am logged in as "Julia"
    And I am on the "bag_yellow" product page
    And I change the "EAN" to "123456789013142"
    And I change the "Size" to "s"
    And I save the product
    And I should not see the text "There are unsaved changes"
    And I visit the "History" column tab
    And I should see 2 versions in the history
    And I should see history:
      | version | author      | property | value           |
      | 2       | Julia Stark | EAN      | 123456789013142 |
      | 2       | Julia Stark | Size     | s               |
    When I revert the product version number 1
    And I visit the "History" column tab
    Then I should see 3 versions in the history
    And I should see history:
      | version | author      | property | value           |
      | 3       | Julia Stark | EAN      | 1234567890131   |
      | 3       | Julia Stark | Size     | xl              |
      | 2       | Julia Stark | EAN      | 123456789013142 |
      | 2       | Julia Stark | Size     | s               |

  Scenario: Changing the parent of a variant product creates a new version of this product
    Given the parent of variant product bag_yellow is changed for rita product model
    When the variant product bag_yellow is reverted to the previous version
    Then the parent of the product bag_yellow should be james
