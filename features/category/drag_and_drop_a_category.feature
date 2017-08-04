@javascript
Feature: Drag and drop a category
  In order to be able to modify the category tree
  As a product manager
  I need to be able to edit a category

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Move category to a different parent in the tree
    Given I am on the categories page
    And I select the "2014 collection" tree
    And I expand the "summer_collection" category
    And I expand the "winter_collection" category
    When I drag the "summer_collection" category to the "winter_boots" category
    Then I should see the "summer_collection" category under the "winter_collection" category
    When I select the "2014 collection" tree
    And I expand the "winter_collection" category
    Then I should see the "summer_collection" category under the "winter_collection" category
