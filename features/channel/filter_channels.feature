@javascript
Feature: Filter channels
  In order to filter channels in the catalog
  As a user
  I need to be able to filter channels in the catalog

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

  Scenario: Successfully display filters
    Given I am on the channels page
    Then I should see the filters Code, Name and Category tree

  Scenario: Successfully filter by code
    Given I am on the channels page
    Then the grid should contain 4 elements
    And I should see channels FOO, BAZ, QUX and BAR
    And I should see the filters Code, Name and Category tree
    When I filter by "Code" with value "BA"
    Then the grid should contain 2 elements
    And I should see channels BAR and BAZ

  Scenario: Successfully filter by name
    Given I am on the channels page
    Then the grid should contain 4 elements
    And I should see channels FOO, BAZ, QUX and BAR
    And I should see the filters Code, Name and Category tree
    When I filter by "Name" with value "Ba"
    Then the grid should contain 2 elements
    And I should see channels BAR and BAZ

  Scenario: Successfully filter by name and code
    Given I am on the channels page
    Then the grid should contain 4 elements
    And I should see channels FOO, BAZ, QUX and BAR
    And I should see the filters Code, Name and Category tree
    When I filter by "Name" with value "Ba"
    And I filter by "Code" with value "R"
    Then the grid should contain 1 element
    And I should see channel BAR

  Scenario: Successfully filter by category
    Given I am on the channels page
    Then the grid should contain 4 elements
    And I should see channels FOO, BAZ, QUX and BAR
    And I should see the filters Code, Name and Category tree
    When I filter by "Category tree" with value "Master"
    Then the grid should contain 2 elements
    And I should see channels FOO and BAR