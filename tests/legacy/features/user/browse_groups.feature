@javascript
Feature: Browse groups
  In order to manage the user groups and rights
  As an administrator
  I need to be able to see user groups

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully display groups
    Given I am on the user groups page
    Then the grid should contain 3 elements
    And I should see the text "Manager"
    And I should see the text "Redactor"
    And I should see the text "IT support"
