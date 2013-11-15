@javascript
Feature: Remove a category
  In order to be able to remove an unused category
  As a user
  I need to be able to remove a category

  Background:
  Given a "footwear" catalog configuration
  And the following products:
    | sku           | categories        |
    | caterpillar_1 | winter_collection |
    | caterpillar_2 | winter_boots      |
  And I am logged in as "admin"

  Scenario: Remove a simple category
    Given I am on the "sandals" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "summer_collection" edit page
    And I should see flash message "Category successfully removed"
    And I should not see the "Sandals" category under the "Summer collection" category

  Scenario: Remove a category with sub-categories
    Given I am on the "winter_collection" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "2014_collection" edit page
    And I should see flash message "Category successfully removed"
    And I should not see "Winter collection"

  Scenario: Remove a category with products linked
    Given I am on the "winter_boots" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "winter_collection" edit page
    And I should see flash message "Category successfully removed"
    When I expand the "Winter collection" category
    Then I should not see "Winter boots"

  Scenario: Remove a category with sub-categories and products linked
    Given I am on the "winter_collection" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be on the category "2014_collection" edit page
    And I should see flash message "Category successfully removed"
    Then I should not see "Winter collection"
    And I should not see "Winter boots"

  Scenario: Remove a category tree
    Given the following category:
      | code            | label           |
      | 2013_collection | 2013 collection |
    And I am on the "2013_collection" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should be redirected on the category tree creation page
    And I should see flash message "Tree successfully removed"

  Scenario: Cancel the removal of a category
    Given I am on the "sandals" category page
    When I press the "Delete" button
    And I cancel the deletion
    Then I should see the "Sandals" category under the "Summer collection" category
