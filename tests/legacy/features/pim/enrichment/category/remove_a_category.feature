@javascript
Feature: Remove a category
  In order to be able to remove an unused category
  As a product manager
  I need to be able to remove a category

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku           | categories                        |
      | caterpillar_1 | winter_collection,2014_collection |
      | caterpillar_2 | winter_boots,2014_collection      |
    And I am logged in as "Julia"

  Scenario: Remove a simple category
    Given I am on the "sandals" category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be on the category "summer_collection" edit page
    And I should see the flash message "Category successfully removed"
    And I should not see the "Sandals" category under the "summer_collection" category

  Scenario: Remove a category tree
    Given the following category:
      | code            | parent | label-en_US     |
      | 2013_collection |        | 2013 collection |
    And I am on the "2013_collection" category page
    And I should see the text "Edit tree - 2013 collection"
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should be redirected on the category tree creation page
    And I should see the flash message "Tree successfully removed"

