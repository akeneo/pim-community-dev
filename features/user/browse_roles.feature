@javascript
Feature: Browse roles
  In order to manage the user roles and rights
  As an administrator
  I need to be able to see user roles

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully display roles
    Given I am on the role index page
    Then I should see the text "Administrator"
    And I should see the text "Catalog manager"
    And I should see the text "User"
    When I click on the "Update" action of the row which contains "User"
    Then I should see the text "User"
