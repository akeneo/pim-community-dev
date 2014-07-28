@javascript
Feature: Check that imported date is properly displayed
  In order to display date information
  As a product manager
  I need to check is the date is properly displayed

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And the following attributes:
      | label   | type | localizable | scopable | useable as grid column |
      | release | date | no          | no       | yes                    |
    And the following products:
      | sku    | release    |
      | postit | 2014-05-01 |

  Scenario: Successfully display a date in the grid
    Given I am on the products page
    And I display the columns sku, family, release, complete, created and updated
    Then the row "postit" should contain:
     | column      | value |
     | release     | May 1, 2014 |

  Scenario: Successfully display a date in the product edit form
    Given I am on the "postit" product page
    Then the field "release" should contain "2014-05-01"
