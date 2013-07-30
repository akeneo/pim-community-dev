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

  Scenario: Successfully display all required attributes in the product creation form
    Given I am on the products page
    And I create a new product
    Then I should see the SKU, Reference, Family and Activated locales fields

  Scenario: Successfully create a product
    Given I am on the products page
    And I create a new product
    And I fill in the following information:
      | SKU               | barbecue  |
      | Reference         | BBQ       |
      | Activated locales | French    |
    And I select the French activated locale
    And I press the "Create" button
    Then I should see "Product successfully saved."
