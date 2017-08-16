@javascript
Feature: Display product attributes in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different attributes in the grid

  Scenario: Successfully display values for simple and multi select attributes
    Given the "footwear" catalog configuration
    And the following product groups:
      | code         | label-en_US  | type    |
      | boots_akeneo | Akeneo Boots | RELATED |
      | no_label     |              | RELATED |
    And the following products:
      | sku         | groups                |
      | black-boots | boots_akeneo          |
      | gray-boots  | no_label              |
      | white-boots | boots_akeneo,no_label |
    When I am logged in as "Julia"
    And I am on the products grid
    Then the row "black-boots" should contain:
      | column | value        |
      | Groups | Akeneo Boots |
    Then the row "gray-boots" should contain:
      | column | value      |
      | Groups | [no_label] |
    Then the row "white-boots" should contain:
      | column | value                    |
      | Groups | Akeneo Boots, [no_label] |
