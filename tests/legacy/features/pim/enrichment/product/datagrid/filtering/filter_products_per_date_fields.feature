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
    And the following families:
      | code     | label-en_US | attributes  |
      | a_family | Family      | sku,release |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by empty value for date attribute
    Given the following products:
      | sku    | family   |release    |
      | postit | a_family |2014-05-01 |
      | book   | a_family |           |
      | mug    | a_family |           |
    And I am on the products grid
    Then the grid should contain 3 elements
    And I should see products postit, book and mug
    And I should be able to use the following filters:
      | filter  | operator     | value | result       |
      | release | is empty     |       | book and mug |
      | release | is not empty |       | postit       |

  @critical
  Scenario: Successfully filter products by date attributes with user timezone America/New_York
    Given the postit product created at "2018-10-03 01:30:00"
    And the mug product created at "2018-10-03 05:30:00"
    And the pen product created at "2018-10-02 23:30:00"
    When I am logged in as "Julia"
    And I am on the products grid
    Then the grid should contain 3 elements
    And I should see products postit, mug and pen
    And I should be able to use the following filters:
      | filter  | operator    | value                     | result         |
      | created | more than   | 10/02/2018                | mug            |
      | created | less than   | 10/03/2018                | postit and pen |
      | created | between     | 10/02/2018 and 10/02/2018 | postit and pen |
      | created | not between | 10/02/2018 and 10/02/2018 | mug            |

