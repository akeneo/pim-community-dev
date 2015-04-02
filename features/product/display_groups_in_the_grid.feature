@javascript
Feature: Display product attributes in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different attributes in the grid

  Scenario: Successfully display values for simple and multi select attributes
    Given the "footwear" catalog configuration
    And the following products:
      | sku          |
      | black-boots  |
      | gray-boots   |
      | white-boots  |
    And the following product groups:
      | code         | label          | type     | products                 |
      | boots_akeneo | Akeneo Boots   | RELATED  | black-boots, white-boots |
      | no_label     |                | RELATED  | gray-boots, white-boots  |
    When I am logged in as "Julia"
    And I am on the products page
    Then the row "black-boots" should contain:
      | column  | value         |
      | Groups  | Akeneo Boots  |
    Then the row "gray-boots" should contain:
      | column  | value         |
      | Groups  | [no_label]    |
    Then the row "white-boots" should contain:
      | column  | value                     |
      | Groups  | Akeneo Boots, [no_label]  |
