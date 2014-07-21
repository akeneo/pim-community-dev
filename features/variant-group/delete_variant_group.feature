@javascript
Feature: Delete a variant group
  In order to manager variant groups for the catalog
  As a product manager
  I need to be able to delete variant groups

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully delete a variant group from the grid
    Given I am on the variant groups page
    Then I should see group caterpillar_boots
    When I click on the "Delete" action of the row which contains "caterpillar_boots"
    And I confirm the deletion
    Then the grid should contain 0 elements
    And I should not see group "caterpillar_boots"

  Scenario: Successfully delete a variant group
    Given I edit the "caterpillar_boots" variant group
    When I press the "Delete" button
    And I confirm the deletion
    Then the grid should contain 0 elements
    And I should not see groups "caterpillar_boots"
