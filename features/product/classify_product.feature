@javascript
Feature: Classify a product
  In order to classify products
  As Julia
  I need to associate a product to categories

  Background:
  Given the following products:
    | sku    |
    | tea    |
    | coffee |
  Given the following categories:
    | code      | title     | parent    |
    | beverages | Beverages |           |
    | hot       | Hot       | beverages |
    | cold      | Cold      | beverages |
    And I am logged in as "Julia"

  Scenario: Associate a product to categories
    And I am logged in as "Julia"
    And I edit the "tea" product
    And I visit the "Categories" tab
    And I select the "Beverages" tree
    And I expand the "Beverages" category
    And I click on the "Hot" category
    And I click on the "Cold" category
    When I press the "Save" button
    Then I should see "Beverages (2)"
     

