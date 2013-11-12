@javascript
Feature: Product creation
  In order to add a non-imported product
  As a user
  I need to be able to manually create a product

  Background:
    Given the following product attribute:
      | label       | required |
      | SKU         | yes      |
      | Reference   | yes      |
      | Description | no       |
    And I am logged in as "admin"
    And I am on the products page

  Scenario: Successfully create a product
    Given I create a new product
    Then I should see the SKU, Reference and Family fields
    And I fill in the following information in the popin:
      | SKU       | barbecue |
      | Reference | BBQ      |
    And I press the "Save" button
    Then I edit the "barbecue" product
    Then I should see "Family: N/A"
    And I should see "Attributes"
    And I should see "Reference"
    And I should see "SKU"
