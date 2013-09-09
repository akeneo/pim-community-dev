@javascript
Feature: Browse roles
  In order to manage the users and rights
  As Peter
  I need to be able to see roles

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully display roles
    Given I am on the roles page
    Then the grid should contain 2 elements


