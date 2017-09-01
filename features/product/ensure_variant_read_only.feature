@javascript
Feature: Disable attribute fields updated by a variant group
  In order to prevent from editing an attribute that will be overriden by a variant group
  As a product manager
  I need should not be able to edit an attributes owned by a variant group

  @skip @info Will be removed in PIM-6444
  Scenario: Successfully display a readonly form for a product in a variant group
    Given the "default" catalog configuration
    And I add the "english" locale to the "mobile" channel
    And the following attributes:
      | code             | label-en_US     | type                         | scopable | localizable | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | options          | Options         | pim_catalog_multiselect      | 1        | 0           |               |                     | other |                  |                  |
      | color            | Color           | pim_catalog_simpleselect     | 0        | 0           |               |                     | other |                  |                  |
      | name             | Name            | pim_catalog_text             | 0        | 1           |               |                     | other |                  |                  |
      | description      | Description     | pim_catalog_textarea         | 0        | 0           |               |                     | other |                  |                  |
      | dimension        | Dimension       | pim_catalog_number           | 1        | 1           |               |                     | other | 0                | 0                |
      | price            | Price           | pim_catalog_price_collection | 0        | 0           |               |                     | other | 0                |                  |
      | length           | Length          | pim_catalog_metric           | 0        | 0           | Length        | CENTIMETER          | other | 0                | 0                |
      | tshirt_material  | Tshirt Material | pim_catalog_simpleselect     | 0        | 0           |               |                     | other |                  |                  |
    And the following "options" attribute options: Blue
    And the following "color" attribute options: Red, black and Green
    And the following "tshirt_material" attribute options: Cotton, Wool
    And the following variant groups:
      | code          | label-en_US    | axis  | type    |
      | tshirt_akeneo | Akeneo T-Shirt | color | VARIANT |
    And the following variant group values:
      | group         | attribute       | value            | locale | scope     |
      | tshirt_akeneo | name            | Great sneakers   | fr_FR  |           |
      | tshirt_akeneo | dimension       | 12               | en_US  | ecommerce |
      | tshirt_akeneo | options         | blue             |        | mobile    |
      | tshirt_akeneo | description     | nice description |        |           |
      | tshirt_akeneo | price-EUR       | 10.0             |        |           |
      | tshirt_akeneo | length          | 15.0 CENTIMETER  |        |           |
      | tshirt_akeneo | tshirt_material | Cotton           |        |           |
    And the following products:
      | sku  | color | groups        |
      | sku1 | red   | tshirt_akeneo |
    And I am logged in as "Julia"
    When I am on the "sku1" product page
    And I switch the scope to "mobile"
    Then the fields Options, Dimension, Price in EUR, Length should be disabled
    Then I should not see the add option link for the "Options" attribute
    Then I should not see the add option link for the "Tshirt Material" attribute
    Given I switch the locale to "fr_FR"
    Then the field name should be disabled
