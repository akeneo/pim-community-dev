@javascript
Feature: Enable and disable a product
  In order to avoid exportation of some products I'm still working on
  As a product manager
  I need to be able to enable or disable a product

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully disable a product
    Given an enabled "boat" product
    When I am on the "boat" product page
    And I disable the product
    Then I should see flash message "Product successfully updated"
    And product "boat" should be disabled

  Scenario: Successfully enable a product
    Given a disabled "boat" product
    When I am on the "boat" product page
    And I enable the product
    Then I should see flash message "Product successfully updated"
    And product "boat" should be enabled

  Scenario: Successfully disable a product from the grid
    Given the following products:
      | sku             | enabled |
      | glass           | yes     |
    Given I am on the products page
    When I click on the "Toggle status" action of the row which contains "glass"
    Then the row "glass" should contain:
      | column        | value    |
      | SKU           | glass    |
      | status        | Disabled |

  Scenario: Successfully enable a product from the grid
    Given the following products:
      | sku             | enabled |
      | glass           | no      |
    Given I am on the products page
    When I click on the "Toggle status" action of the row which contains "glass"
    Then the row "glass" should contain:
      | column        | value   |
      | SKU           | glass   |
      | status        | Enabled |
