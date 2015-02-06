@javascript
Feature: Filter products
  In order to filter products in the catalog
  As a regular user
  I need to be able to filter products with multiples text fields filters

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code      |
      | furniture |
      | library   |
    And the following attributes:
      | code        | label       | type | useable as grid filter |
      | name        | Name        | text | yes                    |
      | description | Description | text | yes                    |
    And the following "name" attribute options: Black and Green
    And the following "description" attribute options: Black and White and Red
    And the following products:
      | sku    | family    | description    | name  |
      | BOOK   | library   |                |       |
      | MUG-1  | furniture | white and blue | green |
      | MUG-2  | furniture | red handle     | green |
      | MUG-3  | furniture | red handle     | green |
      | MUG-4  | furniture | red handle     | green |
      | MUG-5  | furniture |                | green |
      | POST-1 | furniture | red color      |       |
      | POST-2 | furniture | red color      |       |
      | POST-3 | furniture | black color    |       |
    And the following product groups:
      | code   | label  | axis              | type    | products                          |
      | MUG    | Mug    | name, description | VARIANT | MUG-1, MUG-2, MUG-3, MUG-4, MUG-5 |
      | POSTIT | Postit | description       | X_SELL  | POST-1, POST-2, POST-3            |
      | EMPTY  | Empty  |                   | X_SELL  |                                   |
    And I am logged in as "Mary"

  Scenario: Successfully filter products with the sames attributes
    Given I am on the products page
    And I show the filter "Description"
    And I filter by "Description" with value "Red"
    And I show the filter "Name"
    And I filter by "Name" with value "Green"
    Then the grid should contain 3 elements
    And I should see entities "MUG-2" and "MUG-3" and "MUG-4"
    And I hide the filter "Description"
    And I hide the filter "Name"

  Scenario: Successfully filter product without commons attributes
    Given I am on the products page
    And I show the filter "Description"
    And I filter by "Description" with value "black"
    And I show the filter "Name"
    And I filter by "Name" with value "green"
    Then the grid should contain 0 elements
    And I hide the filter "Description"
    And I hide the filter "Name"

  Scenario: Successfully filter only one product
    Given I am on the products page
    And I show the filter "Description"
    And I filter by "Description" with value "white"
    And I show the filter "Name"
    And I filter by "Name" with value "green"
    Then the grid should contain 1 elements
    And I should see entities "MUG-1"
    And I hide the filter "Description"
    And I hide the filter "Name"
