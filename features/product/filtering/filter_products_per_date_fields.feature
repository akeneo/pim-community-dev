@javascript
Feature: Filter products by date field
  In order to filter products by date attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label-en_US | code    | type             | localizable | scopable | useable_as_grid_filter | group |
      | release     | release | pim_catalog_date | 0           | 0        | 1                      | other |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by empty value for date attribute
    Given the following products:
      | sku    | release    |
      | postit | 2014-05-01 |
      | book   |            |
      | mug    |            |
    And the "book" product has the "release" attribute
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | operator     | value | result       |
      | release | is empty     |       | book and mug |
      | release | is not empty |       | postit       |

  Scenario: Successfully filter products by date attributes
    Given the following products:
      | sku    | release    |
      | postit | 2014-05-01 |
      | book   | 2014-05-02 |
      | mug    | 2014-05-03 |
      | tshirt | 2014-05-03 |
      | pen    | 2014-05-06 |
    When I am on the products page
    Then the grid should contain 5 elements
    And I should see products postit, book, mug, tshirt and pen
    And I should be able to use the following filters:
      | filter  | operator    | value                     | result               |
      | release | more than   | 05/02/2014                | mug, tshirt and pen  |
      | release | less than   | 05/03/2014                | postit and book      |
      | release | between     | 05/02/2014 and 05/03/2014 | book, mug and tshirt |
      | release | not between | 05/02/2014 and 05/03/2014 | postit and pen       |

  Scenario: Filter products by date attributes and keep the appropriate default filter values
    Given the following products:
      | sku  | release    |
      | book | 2014-05-02 |
      | pen  | 2014-05-06 |
    And I am on the products page
    And I show the filter "release"
    When I filter by "release" with operator "between" and value "05/01/2014 and 05/03/2014"
    Then the filter "release" should be set to operator "between" and value "05/01/2014 and 05/03/2014"
    And I filter by "created" with operator "" and value ""
    When I click on the "book" row
    And I click back to grid
    Then the filter "release" should be set to operator "between" and value "05/01/2014 and 05/03/2014"
    And I filter by "created" with operator "" and value ""
    When I refresh current page
    Then the filter "release" should be set to operator "between" and value "05/01/2014 and 05/03/2014"
    And I filter by "created" with operator "" and value ""
