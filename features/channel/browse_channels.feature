@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As a user
  I need to be able to see available channels

  Background:
    Given there is no channel
    And the following channels:
      | code | name  | locales      |
      | FOO  | foo   | fr_FR, en_US |
      | BAR  | bar   | de_DE        |
    And I am logged in as "admin"

  Scenario: Successfully display channels
    Given I am on the channels page
    Then the grid should contain 2 elements
    And I should see channels FOO and BAR

  Scenario: Successfully display columns
    Given I am on the channels page
    Then I should see the columns Code, Name and Category tree
