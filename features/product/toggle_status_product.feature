@javascript
Feature: Toggle status of a product
  In order to quick product status toggling
  As a product manager
  I need to be able to enable and disable a product from a product list page

  Background:
    Given the "default" catalog configuration
    And a "CD player" product
    And I am logged in as "Julia"

  Scenario: Disable product from grid
    Given I am on the products grid
    And I should see product CD player
    And the row "CD player" should contain:
      | column | value   |
      | status | ENABLED |
    When I click on the "Toggle status" action of the row which contains "CD player"
    Then the row "CD player" should contain:
      | column | value    |
      | status | DISABLED |
    And I should see the flash message "Product has been disabled"
    When I click on the "Toggle status" action of the row which contains "CD player"
    Then the row "CD player" should contain:
      | column | value   |
      | status | ENABLED |
    And I should see the flash message "Product has been enabled"
