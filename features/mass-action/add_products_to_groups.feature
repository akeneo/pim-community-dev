@javascript
Feature: Add products to many groups at once
  In order to easily organize products into groups
  As a product manager
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
    And I check "Similar boots"
    When I move on to the next step
    Then "similar_boots" group should contain "kickers, hiking_shoes and moon_boots"

  Scenario: Fail to add similar products to a variant group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          | color | size |
      | kickers      | red   | 42   |
      | hiking_shoes | red   | 42   |
    And I am logged in as "Julia"
    And I am on the products page
    Given I mass-edit products kickers, hiking_shoes
    And I choose the "Add to groups" operation
    And I check "Caterpillar boots"
    When I move on to the next step
    Then I should see:
    """
    Group "Caterpillar boots" already contains another product with values "size: 42, color: Red"
    """
