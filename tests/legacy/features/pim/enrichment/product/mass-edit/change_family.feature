@javascript
Feature: Change family of many products at once
  In order to easily organize products into families
  As a product manager
  I need to be able to change the family of many products at once

  Background:
    Given the "footwear" catalog configuration
    And the following families:
      | code     |
      | Food     |
      | Clothing |
    And the following products:
      | sku       | family   |
      | coffee    | Food     |
      | hamburger |          |
      | jeans     | Clothing |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Change the family of many products at once
    Given I select rows coffee and hamburger
    And I press the "Bulk actions" button
    And I choose the "Change family" operation
    And I change the Family to "Food"
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    Then the family of product "coffee" should be "Food"
    And the family of product "hamburger" should be "Food"
