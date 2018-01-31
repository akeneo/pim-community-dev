@javascript
Feature: Import attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to import options for attributes

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | code  | type                     | localizable | scopable | group |
      | fruit | pim_catalog_simpleselect | 0           | 0        | other |
    And I am logged in as "Julia"

  Scenario: Successfully show default translation when blank text
    Given the following CSV file to import:
      """
      code;attribute;sort_order;label-en_US
      red;color;0;
      converse;manufacturer;0;
      """
    And the following job "csv_footwear_option_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_option_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_option_import" job to finish
    And the following product:
      | sku         | family |
      | caterpillar | Boots  |
    And I am on the "caterpillar" product page
    And I visit the "Colors" group
    And I change the "Color" to "[red]"
    Then I should see the text "[red]"
    When I visit the "Product information" group
    And I change the "Manufacturer" to "[Converse]"
    Then I should see the text "[Converse]"
