@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As a user
  I need to be able to see channels

  Background:
    Given there is no channel
    And the following categories:
      | code   | title  |
      | master | Master |
      | mobile | Mobile |
      | ipad   | IPad   |
    And the following channels:
      | code | name  | locales      | category |
      | FOO  | foo   | fr_FR, en_US | master   |
      | BAR  | bar   | de_DE        | master   |
      | BAZ  | baz   | fr_FR        | mobile   |
      | QUX  | qux   | en_US        | ipad     |
    And I am logged in as "admin"

  Scenario: Successfully display channels
    Given I am on the channels page
    Then the grid should contain 4 elements
    And I should see channels FOO, BAR, BAZ and QUX

  Scenario: Successfully display columns
    Given I am on the channels page
    Then I should see the columns Code, Name and Category tree
