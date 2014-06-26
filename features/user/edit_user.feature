@javascript
Feature: Edit a user
  In order to manage the users and rights
  As an administrator
  I need to be able to edit a user

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a user
    Given I edit the "admin" user
    When I fill in the following information:
      | First name | John  |
      | Last name  | Smith |
      | Owner      | Main  |
    And I save the user
    Then I should see "User saved"
    And I should see "John Smith"

  @javascript
  Scenario: Successfully edit and apply user preferences
    And an enabled "teapot" product
    When I edit the "Peter" user
    And I visit the "Additional" tab
    And I fill in the following information:
      | Catalog locale | de_DE           |
      | Catalog scope  | Print           |
      | Default tree   | 2015 collection |
    And I save the user
    When I am on the products page
    Then I should see "Products / DE"
    And I should see "Print"
    And I should see "2015 Männer-Kollektion"
    And I should see "2015 Damenkollektion"
