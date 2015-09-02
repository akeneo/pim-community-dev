@javascript
Feature: Apply permissions on assets picker
  In order to apply permissions on assets picker
  As a redactor
  I want to see assets only when I have the rights to see them

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   |
      | shirt |

  Scenario: Successfully show granted assets in asset picket as a redactor
    Given I am logged in as "Mary"
    And I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 25
    Then the grid should contain 11 elements

  Scenario: Successfully show granted assets in asset picket as a manager
    Given I am logged in as "Julia"
    And I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 25
    Then the grid should contain 15 elements
