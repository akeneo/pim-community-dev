@javascript
Feature: List categories
  In order to be able to see the categories in my catalog
  As a product manager
  I need to be able to list existing categories

  Scenario: Successfully display categories
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the categories page
    Then I should see the text "2014 collection"
    And I should see the text "Summer collection"
    And I should see the text "Winter collection"
    And I should see the text "Please select a category on the left or Create a new category"

  Scenario: Click on a category without the right permissions do nothing
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Edit a category and Create a category
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the categories page
    Then I should not see "Please select a category on the left or Create a new category"
    When I click on the "summer_collection" category
    Then I should not see the text "Server error"
