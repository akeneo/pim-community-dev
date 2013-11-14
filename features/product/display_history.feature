@javascript
Feature: Display the product history
  In order to know who, when and what changes has been made to a product
  As Julia
  I need to have access to a product history

  Scenario: Display product updates
    Given the following product attributes:
      | label        | required |
      | SKU          | yes      |
      | Brand        | no       |
      | Manufacturer | no       |
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU | cam |
    And I press the "Save" button
    And I edit the "cam" product
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
    | action | version | data    |
    | create | 1       | sku:cam |
