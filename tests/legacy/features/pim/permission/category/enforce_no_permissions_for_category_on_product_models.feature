@javascript
Feature: Enforce no permissions for a category
  In order to be able to prevent some users from viewing some product models
  As an administrator
  I need to be able to enforce no permissions for categories

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: Display only granted product models in products grid, I see all product models
    Given I am logged in as "Julia"
    When I am on the products grid
    And I open the category tree
    And I select the "Master" tree
    And I filter by "category" with operator "" and value "Men"
    And I filter by "parent" with operator "is empty" and value ""
    And I sort by "family" value ascending
    And I close the category tree
    Then I should see the product models plain and jack

  Scenario: Display only granted product models in products grid, I see a sub set of product models
    Given I am logged in as "Julia"
    When I am on the "tshirts" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products | Redactor |
      | Allowed to own products  | Redactor |
    And I save the category
    And I am on the products grid
    And I open the category tree
    And I select the "Master" tree
    And I filter by "category" with operator "" and value "Men"
    And I close the category tree
    And I filter by "family" with operator "in list" and value "Clothing"
    Then I should not see the product models plain

  @critical
  Scenario: A user can't see a product model if he doesn't have view permission on at least of one its categories
    Given I am logged in as "Julia"
    And I am on the "tshirts" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products | Redactor |
      | Allowed to own products  | Redactor |
    And I save the category
    Then I can't access to "plain" product model page
