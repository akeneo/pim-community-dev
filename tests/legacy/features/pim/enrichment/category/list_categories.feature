@javascript
Feature: List categories
  In order to be able to see the categories in my catalog
  As a product manager
  I need to be able to list existing categories

  @critical
  Scenario: Successfully display categories
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the categories page
    Then I should see the text "2014 collection"
    And I should see the text "Summer collection"
    And I should see the text "Winter collection"
    And I should see the text "Please select a category on the left or Create a new category"
