@javascript
Feature: Add products to variant group via a form
  In order to add products in variant group
  As a product manager
  I need to be able to add products to variant group via a form

  Scenario: Add products to a variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          |
      | kickers      |
      | hiking_shoes |
      | moon_boots   |
    And I am logged in as "Julia"
    And I am on the products page
    Given I mass-edit products kickers, hiking_shoes and moon_boots
    And I choose the "Add to a variant group" operation
    And I select the "Caterpillar boots" variant group
    When I move on to the next step
    Then I should be on the products page
