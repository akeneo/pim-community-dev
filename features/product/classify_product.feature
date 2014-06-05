@javascript
Feature: Classify a product
  In order to classify products
  As a product manager
  I need to associate a product to categories

  Background:
    Given the "default" catalog configuration
    And the following products:
      | sku    |
      | tea    |
      | coffee |
    Given the following categories:
      | code      | label-en_US | parent    |
      | beverages | Beverages   |           |
      | hot       | Hot         | beverages |
      | cold      | Cold        | beverages |
    And I am logged in as "Julia"

  Scenario: Associate a product to categories
    Given I edit the "tea" product
    When I visit the "Categories" tab
    And I select the "Beverages" tree
    And I expand the "Beverages" category
    And I click on the "Hot" category
    And I click on the "Cold" category
    And I press the "Save" button
    Then I should see "Beverages (2)"
