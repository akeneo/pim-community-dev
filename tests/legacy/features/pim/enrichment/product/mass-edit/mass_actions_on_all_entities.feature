@javascript
Feature: Apply a mass action on all entities
  In order to modify all items
  As a product manager
  I need to be able to select all entities in the grid and apply mass-edit on them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family  | name-en_US   | categories   | price          | size | color |
      | super_boots | boots   | Super boots  | winter_boots | 10 USD, 15 EUR | 35   | blue  |
      | mega_boots  | boots   | Mega boots   | winter_boots | 10 USD, 15 EUR | 46   | red   |
      | ultra_boots | boots   | Ultra boots  | winter_boots |                | 36   | black |
      | sandals     | sandals | Tiny sandals | sandals      | 10 USD, 15 EUR | 42   | red   |
    And I am logged in as "Julia"

  @critical
  Scenario: Edit family of all products, filtered by category and completeness
    When I am on the products grid
    And I hide the filter "family"
    And I open the category tree
    And I filter by "category" with operator "" and value "2014_collection"
    And I filter by "category" with operator "" and value "winter_collection"
    And I close the category tree
    And I switch the scope to "Mobile"
    And I filter by "completeness" with operator "" and value "yes"
    And I select rows mega_boots
    When I select all entities
    And I press the "Bulk actions" button
    And I choose the "Change family" operation
    And I change the Family to "Sandals"
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    Then the family of product "super_boots" should be "sandals"
    Then the family of product "mega_boots" should be "sandals"
    And the family of product "ultra_boots" should be "boots"
