@javascript
Feature: Check that imported date is properly displayed
  In order to display date information
  As a product manager
  I need to have dates properly displayed

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And the following attributes:
      | label   | type | localizable | scopable |
      | release | date | no          | no       |
    And the following products:
      | sku    | release    |
      | postit | 2014-05-01 |

  Scenario: Successfully display a date in the grid (PIM-2971)
    Given I am on the products page
    And I display the columns sku, family, release, complete, created and updated
    Then the row "postit" should contain:
     | column  | value       |
     | release | May 1, 2014 |

  Scenario: Successfully display a date in the product edit form (PIM-2971)
    Given I am on the "postit" product page
    Then the field release should contain "2014-05-01"

  Scenario: Do not change date in history if the date has not been changed in the product (PIM-3009)
    Given I am on the "postit" product page
    And I fill in the following information:
        | SKU | nice_postit |
    And I press the "Save" button
    When I open the history
    Then I should see history:
      | version | property | before | after       |
      | 2       | SKU      | postit | nice_postit |
      | 1       | SKU      |        | postit      |
      | 1       | release  |        | 2014-05-01  |
      | 1       | enabled  |        | 1           |
