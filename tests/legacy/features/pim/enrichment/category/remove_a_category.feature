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

  Scenario: Remove a simple category via the edit page
    Given I am on the "sandals" category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    And I should see the text "The category \"Sandals\" was successfully deleted"
    And I should see the text "2014 collection"

  Scenario: Remove a category tree via the grid
    Given the following category:
      | code            | parent | label-en_US     |
      | 2013_collection |        | 2013 collection |
    And I am on the categories page
    And I should see the text "2013 collection"
    And I should see the text "2014 collection"
    When I hover over the category "2013 collection"
    And I press the "Delete" button
    And I confirm the deletion
    Then I should see the text "The tree \"2013 collection\" was successfully deleted"
    And I should not see the text "2013 collection"
    But I should see the text "2014 collection"
