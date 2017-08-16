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
      | code        | label-en_US | type             | useable_as_grid_filter | group |
      | name        | Name        | pim_catalog_text | 1                      | other |
      | description | Description | pim_catalog_text | 1                      | other |
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
    And I am on the products grid
    And I show the filter "description"
    And I show the filter "name"

  Scenario: Successfully filter products with the sames attributes
    Given I filter by "description" with operator "contains" and value "Red"
    And I should be able to use the following filters:
      | filter | operator         | value | result                                 |
      | name   | is empty         |       | POST-1 and POST-2                      |
      | name   | is not empty     |       | MUG-2, MUG-3 and MUG-4                 |
      | name   | contains         | in    | MUG-2, MUG-3 and MUG-4                 |
      | name   | starts with      | in    | MUG-2 and MUG-3                        |
      | name   | does not contain | in    |                                        |
      | name   | does not contain | green | MUG-2, MUG-3 and MUG-4                 |
      | name   | is equal to      | in    |                                        |
      | name   | is equal to      | pink  | MUG-4                                  |
    And I hide the filter "description"
    And I hide the filter "name"

  Scenario: Successfully filter product without commons attributes
    Given I filter by "name" with operator "contains" and value "indigo"
    And I should be able to use the following filters:
      | filter      | operator         | value      | result                         |
      | description | is empty         |            | MUG-5                          |
      | description | is not empty     |            | MUG-1, MUG-2, MUG-3 and POST-3 |
      | description | contains         | color      | POST-3                         |
      | description | contains         | red        | MUG-2 and MUG-3                |
      | description | starts with      | color      |                                |
      | description | starts with      | b          | POST-3                         |
      | description | does not contain | bl         | MUG-2 and MUG-3                |
      | description | is equal to      | red        |                                |
      | description | is equal to      | red handle | MUG-2 and MUG-3                |
    And I hide the filter "description"
    And I hide the filter "name"
