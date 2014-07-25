@javascript
Feature: Check that imported date is properly displayed
  In order to display date information
  As a product manager
  I need to check is the date is properly displayed

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label   | type | localizable | scopable | useable as grid column |
      | release | date | no          | no       | yes                    |
    And the following products:
      | sku    | release    |
      | postit | 2014-05-01 |
    And I am logged in as "Julia"

  Scenario: Successufilly display a date in the grid
    Given I am on the products page
    Then the row "postit" should contain:
     | column      | value |
     | release     | 2014-05-01 |


