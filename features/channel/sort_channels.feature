@javascript
Feature: Sort channels
  In order to sort channels in the catalog
  As a user
  I need to be able to sort channels by several columns in the catalog

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

  Scenario: Successfully display the sortable columns
    Given I am on the channels page
    Then the datas can be sorted by code, name and category tree
    And the datas are sorted ascending by code
    And I should see sorted channels BAR, BAZ, FOO and QUX

  Scenario: Successfully sort channels by code ascending
    Given I am on the channels page
    When I sort by "code" value ascending
    Then I should see sorted channels BAR, BAZ, FOO and QUX

  Scenario: Successfully sort channels by code descending
    Given I am on the channels page
    When I sort by "code" value descending
    Then I should see sorted channels QUX, FOO, BAZ and BAR

  Scenario: Successfully sort channels by name ascending
    Given I am on the channels page
    When I sort by "name" value ascending
    Then I should see sorted channels BAR, BAZ, FOO and QUX

  Scenario: Successfully sort channels by name descending
    Given I am on the channels page
    When I sort by "name" value descending
    Then I should see sorted channels QUX, FOO, BAZ and BAR

  @skip
  Scenario: Successfully sort channels by tree ascending
    Given I am on the channels page
    When I sort by "category tree" value ascending
    Then I should see sorted channels QUX, FOO, BAR and BAZ

  @skip
  Scenario: Successfully sort channels by tree descending
    Given I am on the channels page
    When I sort by "category tree" value descending
    Then I should see sorted channels BAZ, FOO, BAR and QUX
