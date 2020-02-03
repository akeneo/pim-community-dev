@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product with reference data
  As a product manager
  I need to have access to a product history

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | heels |
      | Family | Heels |
    And I press the "Save" button in the popin
    And I wait to be on the "heels" product page

  Scenario: Add an available "simple select" reference data to a product
    Given I visit the "Other" group
    And I change the "Heel color" to "UA Red"
    And I save the product
    When I visit the "History" column tab
    Then I should see history:
      | version | property   | value  | date |
      | 2       | Heel color | ua-red | now  |
    When I visit the "Attributes" column tab
    And I change the "Heel color" to "Green"
    And I save the product
    And I visit the "History" column tab
    Then I should see history:
      | version | property   | value                | date |
      | 3       | Heel color | green-html-css-color | now  |
