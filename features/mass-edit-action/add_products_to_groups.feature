@javascript
Feature: Add products to many groups at once
  In order to easily organize products into groups
  As Julia
  I need to be able to add products to many groups at once

  Scenario: Add products to a none variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          |
      | kickers      |
      | hiking_shoes |
      | moon_boots   |
    And I am logged in as "Julia"
    And I am on the products page
    Given I mass-edit products kickers, hiking_shoes and moon_boots
    And I choose the "Add to groups" operation
    And I check "Caterpillar boots"
    When I move on to the next step
    Then "caterpillar_boots" group should contain "kickers, hiking_shoes and moon_boots"
