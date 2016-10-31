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
    And I am on the products page

  Scenario: Change the family of many products at once
    Given I select rows coffee and hamburger
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change the family of products" operation
    And I change the Family to "Food"
    And I move on to the next step
    And I wait for the "change-family" mass-edit job to finish
    Then the family of product "coffee" should be "Food"
    And the family of product "hamburger" should be "Food"

  Scenario: Remove many products from a product family
    Given I select rows coffee, hamburger and jeans
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change the family of products" operation
    And I change the Family to "None"
    And I move on to the next step
    And I wait for the "change-family" mass-edit job to finish
    Then the family of product "coffee" should be ""
    And the family of product "hamburger" should be ""
    And the family of product "jeans" should be ""
