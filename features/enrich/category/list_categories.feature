@javascript
Feature: List categories
  In order to be able to see the categories in my catalog
  As a product manager
  I need to be able to list existing categories

  Scenario: Successfully display categories
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the categories page
    Then I should see "2014 collection"
    And I should see "Summer collection"
    And I should see "Winter collection"
    And I should see "Please select a category on the left or Create a new category"

  Scenario: Click on a category without the right permissions do nothing
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the "Administrator" role page
    And I remove rights to Edit a category
    And I remove rights to Create a category
    And I save the role
    And I wait 5 seconds
    Given I am on the categories page
    Then I should not see "Please select a category on the left or Create a new category"
    When I click on the "Summer collection" category
    Then I should not see "Server error"
    Then I reset the "Administrator" rights
