Feature: Remove a category
  In order to be able to remove an unused category
  As a user
  I need to be able to remove a category

  Background:
  Given the following category:
    | code        | title       |
    | computers   | Computers   |
  And I am logged in as "admin"

  @javascript
  Scenario: Remove a category
    Given I am on the "computers" category page
    When I press the "Delete" button
    And I confirm the deletion
    Then I should see "Category successfully removed"
