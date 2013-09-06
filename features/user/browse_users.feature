@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As a user
  I need to be able to see channels

  Background:
    Given I am logged in as "admin"

  Scenario: Successfully display users
    Given I am on the users page
    Then the grid should contain 1 element