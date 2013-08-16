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

  Scenario: Successfully sort channels by code ascending
    Given I am on the channels page
    Then I should see sorted channels BAR, BAZ, FOO and QUX
    When I sort by "code" value ascending
    Then I should see sorted channels BAR, BAZ, FOO and QUX

  Scenario: Successfully sort channels by code descending
    Given I am on the channels page
    Then I should see sorted channels BAR, BAZ, FOO and QUX
    When I sort by "code" value descending
    Then I should see sorted channels QUX, FOO, BAZ and BAR
