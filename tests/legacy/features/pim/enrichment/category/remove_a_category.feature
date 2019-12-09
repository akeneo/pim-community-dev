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

  @jira https://akeneo.atlassian.net/browse/PIM-4227
  Scenario: Remove a category with linked products limit exceeded
    Given the following products:
      | sku            | categories        |
      | caterpillar_1  | winter_collection |
      | caterpillar_2  | winter_collection |
      | caterpillar_3  | winter_collection |
      | caterpillar_4  | winter_collection |
      | caterpillar_5  | winter_collection |
      | caterpillar_6  | winter_collection |
      | caterpillar_7  | winter_collection |
      | caterpillar_8  | winter_collection |
      | caterpillar_9  | winter_collection |
      | caterpillar_10 | winter_collection |
      | caterpillar_11 | winter_collection |
      | caterpillar_12 | winter_collection |
      | caterpillar_13 | winter_collection |
      | caterpillar_14 | winter_collection |
      | caterpillar_15 | winter_collection |
      | caterpillar_16 | winter_collection |
      | caterpillar_17 | winter_collection |
      | caterpillar_18 | winter_collection |
      | caterpillar_19 | winter_collection |
      | caterpillar_20 | winter_collection |
      | caterpillar_21 | winter_collection |
    And I am on the "winter_collection" category page
    When I press the secondary action "Delete"
    Then I should see a confirm dialog with the following content:
      | title   | Confirm deletion                                                                           |
      | content | This category contains more products than allowed for this operation (20 products maximum) |
