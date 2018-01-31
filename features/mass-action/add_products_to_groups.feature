@javascript
Feature: Add products to many groups at once via a form
  In order to easily organize products into groups
  As a product manager
  I need to be able to add products to many groups at once via a form

  Scenario: Add products to a related group
    Given the "footwear" catalog configuration
    And the following products:
      | sku          |
      | kickers      |
      | hiking_shoes |
      | moon_boots   |
    And I am logged in as "Julia"
    And I am on the products grid
    Given I select rows kickers, hiking_shoes and moon_boots
    And I press the "Bulk actions" button
    And I choose the "Add to groups" operation
    And I check "Similar boots"
    When I confirm mass edit
    And I wait for the "add_product_value" job to finish
    Then "similar_boots" group should contain "kickers, hiking_shoes and moon_boots"
