@javascript
Feature: Browse group types
  In order to list the existing group types in the catalog
  As a user
  I need to be able to see group types

  Background:
    Given there is no group type
    And the following group types:
      | code    | label   |
      | related | Related |
      | special | Special |
    And I am logged in as "admin"

  Scenario: Successfully display group types
    Given I am on the group types page
    Then the grid should contain 2 elements
    And I should see the columns Code and Label
    And I should see group types related, special
