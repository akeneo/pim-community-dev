@javascript
Feature: Filter products by date field
  In order to filter products by date attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully filter products by empty value for date attribute
    Given the following attributes:
      | label   | type | localizable | scopable | useable as grid filter |
      | release | date | no          | no       | yes                    |
    And the following products:
      | sku    | release    |
      | postit | 2014-05-01 |
      | book   |            |
      | mug    |            |
    And the "book" product has the "release" attribute
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | value | result       |
      | release | empty | book and mug |

  @skip @info Broken because timezone is mot well managed yet (except for UTC)
  Scenario: Successfully filter products by date attributes
    Given the following attributes:
      | label   | code    | type | localizable | scopable | useable as grid filter | useable as grid column |
      | release | release | date | no          | no       | yes                    | yes                    |
    And the following products:
      | sku    | release    |
      | postit | 2014-05-01 |
      | book   | 2014-05-02 |
      | mug    | 2014-05-03 |
      | tshirt | 2014-05-03 |
      | pen    | 2014-05-06 |
    And I am on the products page
    Then the grid should contain 5 elements
    And I should see products postit, book, mug, tshirt and pen
    And I should be able to use the following filters:
      | filter  | value                                 | result                  |
      | release | more than 2014-05-02                  | mug and tshirt and pen  |
      | release | less than 2014-05-03                  | postit and book         |
      | release | between 2014-05-02 and 2014-05-03     | book and mug and tshirt |
      | release | not between 2014-05-02 and 2014-05-03 | postit and pen          |
