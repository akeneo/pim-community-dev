@javascript
Feature: Revert an assets collection in a product
  In order to revert a collection of assets
  As a product manager

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert an assets collection in a product
    Given the following product:
      | sku   | family  |
      | jeans | jackets |
    When I edit the "jeans" product
    And I visit the "Media" group
    And I start to manage assets for "gallery"
    And I change the page size to 100
    And I check the row "machine"
    And I check the row "minivan"
    Then the asset basket should contain minivan, machine
    And I confirm the asset modification
    Then the "gallery" asset gallery should contain machine, minivan
    When I save the product
    And I open the history
    Then I should see history:
      | version | property | value           |
      | 2       | gallery  | machine,minivan |
    When I close the "History" panel
    And I start to manage assets for "gallery"
    And I change the page size to 100
    And I uncheck the row "machine"
    Then the asset basket should contain minivan
    And I confirm the asset modification
    Then the "gallery" asset gallery should contain minivan
    When I save the product
    And I open the history
    Then I should see history:
      | version | property | value   |
      | 3       | gallery  | minivan |
    When I revert the product version number 2
    Then the product "jeans" should have the following values:
      | gallery | machine, minivan |
