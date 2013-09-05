Feature: Edit a category
  In order to be able to modify the category tree
  As a user
  I need to be able to edit a category

  Background:
  Given the following categories:
    | code        | title       | parent    |
    | computers   | Computers   |           |
    | laptops     | Laptops     | computers |
    | hard_drives | Hard drives | laptops   |
    | pc          | PC          | computers |
  And I am logged in as "admin"

  Scenario: Edit a category
    Given I edit the "Laptops" category
    And I change the Code to "notebooks"
    And I save the category
    Then I should be on the category "notebooks" edit page
    And I should see "Category successfully updated"

  @javascript
  Scenario: Go to category edit page from the category tree
    Given I am on the categories page
    And I click on the "Computers" category
    Then I should be on the category "computers" edit page

  @javascript
  Scenario: Move category to a different position in the tree
    Given I am on the categories page
    And I expand the "Laptops" category
    And I drag the "Hard drives" category to the "Computers" category
    Then I should see the "Hard drives" category under the "Computers" category
