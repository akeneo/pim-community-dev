@javascript
Feature: Editing attribute values of a variant group also updates products with reference data
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to change reference data attribute values of a variant group

  # what's tested here?
  # --------------------------------|-------------|
  # TYPE                            | VALID VALUE |
  # --------------------------------|-------------|
  # pim_reference_data_simpleselect | done        |
  # pim_reference_data_multiselect  | done        |

  Background:
    Given a "footwear" catalog configuration
    And the following variant group values:
      | group             | attribute          | value         | locale | scope  |
      | caterpillar_boots | destocking_date    | 2012-02-22    |        |        |
      | caterpillar_boots | length             | 10 CENTIMETER |        |        |
      | caterpillar_boots | weather_conditions | Dry           |        |        |
      | caterpillar_boots | number_in_stock    | 1900          |        |        |
      | caterpillar_boots | price              | 39.99 EUR     |        |        |
      | caterpillar_boots | rating             | 1             |        |        |
      | caterpillar_boots | name               | Old name      | en_US  |        |
      | caterpillar_boots | description        | A product.    | en_US  | tablet |
    And the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 40   |
    And the following attributes:
      | code                  | label-en_US           | type             | group | allowed_extensions |
      | technical_description | Technical description | pim_catalog_file | media | txt                |
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab

  @skip @info Will be removed in PIM-6444
  Scenario: Change a pim_reference_data_simpleselect attribute of a variant group
    Given the following reference data:
      | type  | code      | label |
      | color | ABJP44823 | Red   |
      | color | APC40     |       |
    When I add available attributes Heel color
    And I visit the "Other" group
    And I change the "Heel color" to "Red"
    And I save the variant group
    And I am on the "boot" product page
    And I visit the "Other" group
    Then the product Heel color should be "ABJP44823"

  @skip @info Will be removed in PIM-6444
  Scenario: Change a pim_reference_data_multiselect attribute of a variant group
    Given the following reference data:
      | type   | code   | label  |
      | fabric | gold   | Gold   |
      | fabric | smooth |        |
      | fabric | crispy | Crispy |
    When I add available attributes Sole fabric
    And I visit the "Other" group
    And I change the "Sole fabric" to "Gold, smooth"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    And I am on the "boot" product page
    And I visit the "Other" group
    Then the product Sole fabric should be "gold, smooth"
