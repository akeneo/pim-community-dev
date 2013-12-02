Feature: Edit a category
  In order to be able to modify the category tree
  As a user
  I need to be able to edit a category

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit a category
    Given I edit the "Sandals" category
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My sandals |
    And I save the category
    Then I should be on the category "sandals" edit page
    And I should see "My sandals"

  @javascript
  Scenario: Go to category edit page from the category tree
    Given I am on the categories page
    And I select the "2014 collection" tree
    And I click on the "Summer collection" category
    Then I should be on the category "summer_collection" edit page

  @javascript @info This scenario does not accurately describe dragging because of limited behat/selenium dragging capabilities
  Scenario: Move category to a different position in the tree
    Given I am on the categories page
    And I select the "2014 collection" tree
    And I expand the "Summer collection" category
    And I expand the "Winter collection" category
    And I drag the "Summer collection" category to the "Winter boots" category
    Then I should see the "Summer collection" category under the "Winter collection" category

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I edit the "winter_boots" category
    When I fill in the following information:
      | English (United States) | My winter boots |
    Then I should see "There are unsaved changes."
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                    |
      | content | You will lose changes to the category if you leave the page. |
