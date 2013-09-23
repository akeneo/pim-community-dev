@javascript
Feature: Change family of many products at once
  In order to easily organize products into families
  As Julia
  I need to be able to change the family of many products at once

  Background:
    Given the following family:
      | code     |
      | Food     |
      | Clothing |
    And the following products:
      | sku       | family    |
      | coffee    | Food      |
      | hamburger |           |
      | jeans     | Clothing  |

  Scenario: Change the family of many products at once
    Given I am logged in as "Julia"
    And I am on the products page
    When I mass-edit products coffee and hamburger
    And I choose the "Change the family of products" operation
    And I change the Family to "Food"
    When I validate the mass action
    Then the family of product "coffee" should be "Food"
    And the family of product "hamburger" should be "Food"

  Scenario: Remove many products from a product family
    Given I am logged in as "Julia"
    And I am on the products page
    When I mass-edit products coffee, hamburger and jeans
    And I choose the "Change the family of products" operation
    And I change the Family to "None"
    When I validate the mass action
    Then the product "coffee" should have no family
    And the product "hamburger" should have no family
    And the product "jeans" should have no family
