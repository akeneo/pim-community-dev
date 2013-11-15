Feature: Edit a user
  In order to manage the users and rights
  As Peter
  I need to be able to edit a user

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit a user
    Given I edit the "admin" user
    When I fill in the following information:
      | First name | John  |
      | Last name  | Smith |
    And I select "Main" from "Owner"
    And I save the user
    Then I should see "John Smith"

  @javascript
  Scenario: Successfully edit and apply user preferences
    Given the following categories:
      | code        | label       | parent      |
      | books       | Books       |             |
      | kitchenware | Kitchenware |             |
      | kettle      | Kettle      | kitchenware |
    And an enabled "teapot" product
    When I edit the "admin" user
    And I select "fr_FR" from "Catalog locale"
    And I select "mobile" from "Catalog scope"
    And I select "kitchenware" from "Default tree"
    And I select "Main" from "Owner"
    And I save the user
    When I am on the products page
    Then I should see "Products / FR"
    And I should see "Mobile"
    And I should see "kettle"
