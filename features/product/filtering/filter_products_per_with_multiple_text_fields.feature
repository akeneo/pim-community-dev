@javascript
Feature: Filter products with multiples text fields filters
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
      | code        | label       | type | useable_as_grid_filter |
      | name        | Name        | text | yes                    |
      | description | Description | text | yes                    |
    And the following products:
      | sku    | family    | description    | name   |
      | BOOK   | library   |                |        |
      | MUG-1  | furniture | white and blue | indigo |
      | MUG-2  | furniture | red handle     | indigo |
      | MUG-3  | furniture | red handle     | indigo |
      | MUG-4  | furniture | red handle     | pink   |
      | MUG-5  | furniture |                | indigo |
      | POST-1 | furniture | red color      |        |
      | POST-2 | furniture | red color      |        |
      | POST-3 | furniture | black color    | indigo |
    And I am logged in as "Mary"
    And I am on the products page
    And I show the filter "Description"
    And I show the filter "Name"

  Scenario: Successfully filter products with the sames attributes
    Given I filter by "Description" with value "Red"
    And I should be able to use the following filters:
      | filter | value                  | result                 |
      | Name   | empty                  | POST-1 and POST-2      |
      | Name   | contains in            | MUG-2, MUG-3 and MUG-4 |
      | Name   | starts with in         | MUG-2 and MUG-3        |
      | Name   | ends with nk           | MUG-4                  |
      | Name   | ends with NK           | MUG-4                  |
      | Name   | does not contain in    |                        |
      | Name   | does not contain green | MUG-2, MUG-3 and MUG-4 |
      | Name   | is equal to in         |                        |
      | Name   | is equal to pink       | MUG-4                  |
    And I hide the filter "Description"
    And I hide the filter "Name"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "Name" with value "indigo"
    And I should be able to use the following filters:
      | filter      | value                  | result          |
      | Description | empty                  | MUG-5           |
      | Description | contains color         | POST-3          |
      | Description | contains red           | MUG-2 and MUG-3 |
      | Description | starts with color      |                 |
      | Description | starts with b          | POST-3          |
      | Description | ends with or           | POST-3          |
      | Description | ends with OR           | POST-3          |
      | Description | does not contain bl    | MUG-2 and MUG-3 |
      | Description | is equal to red        |                 |
      | Description | is equal to red handle | MUG-2 and MUG-3 |
    And I hide the filter "Description"
    And I hide the filter "Name"
