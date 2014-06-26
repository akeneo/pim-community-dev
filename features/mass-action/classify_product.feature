@javascript
Feature: Classify many products at once
  In order to easily classify products
  As a product manager
  I need to associate many products to categories at once

  Background:
    Given the "default" catalog configuration
    And the following products:
      | sku    |
      | beer   |
      | wine   |
    And the following categories:
      | code         | label-en_US   | parent    |
      | beverages    | Beverages     |           |
      | hot          | Hot           | beverages |
      | cold         | Cold          | beverages |
      | alcoholic    | Alcoholic     | beverages |
      | nonalcoholic | Non alcoholic | beverages |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Classify many products at once
    Given I mass-edit products wine and beer
    And I choose the "Classify products in categories" operation
    And I select the "Beverages" tree
    And I expand the "Beverages" category
    And I click on the "Cold" category
    And I click on the "Alcoholic" category
    When I move on to the next step
    And I am on the products page
    And I select the "Beverages" tree
    Then I should see "Alcoholic (2)"
    And I should see "Cold (2)"

