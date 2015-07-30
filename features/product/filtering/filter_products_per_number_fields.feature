@javascript
Feature: Filter products by number field
  In order to filter products by number attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully filter products by empty value for number attributes
    Given the following attributes:
      | label | type   | localizable | scopable | useable_as_grid_filter | decimals_allowed |
      | count | number | no          | no       | yes                    | no               |
      | rate  | number | no          | no       | yes                    | yes              |
    And the following products:
      | sku    | count | rate |
      | postit | 200   |      |
      | book   |       | 9.5  |
      | mug    |       |      |
    And the "book" product has the "count" attribute
    And the "mug" product has the "rate" attribute
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter | value   | result         |
      | count  | empty   | book and mug   |
      | count  | > 200   |                |
      | count  | < 200   |                |
      | count  | > 199   | postit         |
      | count  | < 201   | postit         |
      | count  | >= 200  | postit         |
      | count  | <= 200  | postit         |
      | count  | = 200   | postit         |
      | count  | = 0     |                |
      | count  | > 0     | postit         |
      | rate   | empty   | mug and postit |
      | rate   | > 9.5   |                |
      | rate   | <= 9.5  | book           |
      | rate   | = 0     |                |
      | rate   | > 0     | book           |
