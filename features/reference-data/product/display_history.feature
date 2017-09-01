@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product with reference data
  As a product manager
  I need to have access to a product history

  Background:
    Given a "footwear" catalog configuration
    And the following "heel_color" attribute reference data: Red, Green, Black
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a new product
    And I fill in the following information in the popin:
      | SKU    | heels |
      | family | Heels |
    And I press the "Save" button in the popin
    And I wait to be on the "heels" product page

  Scenario: Add an available "simple select" reference data to a product
    Given I visit the "Other" group
    And I change the "Heel color" to "Red"
    And I save the product
    When I visit the "History" column tab
    Then I should see history:
      | version | property   | value |
      | 2       | Heel color | Red   |
    When I visit the "Attributes" column tab
    And I change the "Heel color" to "Green"
    And I save the product
    And I visit the "History" column tab
    Then I should see history in panel:
      | version | property   | value |
      | 3       | Heel color | Green |

  Scenario: Add an available "multi select" reference data to a product
    Given I visit the "Other" group
    And I change the "Sole fabric" to "Nylon,PVC"
    And I save the product
    When I visit the "History" column tab
    Then I should see history in panel:
      | version | property    | value     |
      | 2       | Sole fabric | Nylon,PVC |
