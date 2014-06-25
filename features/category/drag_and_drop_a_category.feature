@skip
Feature: Drag and drop a category
  In order to be able to modify the category tree
  As a product manager
  I need to be able to edit a category

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript @info This scenario does not accurately describe dragging because of limited behat/selenium dragging capabilities
  Scenario: Move category to a different position in the tree
    Given I am on the categories page
    And I select the "2014 collection" tree
    And I expand the "Summer collection" category
    And I expand the "Winter collection" category
    And I drag the "Summer collection" category to the "Winter boots" category
    Then I should see the "Summer collection" category under the "Winter collection" category
