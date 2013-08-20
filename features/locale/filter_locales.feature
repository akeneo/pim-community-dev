@javascript
Feature: Filter locales
  In order to filter locales in the catalog
  As an user
  I need to be able to filter locales in the catalog

  Background:
    Given the following locales:
      | code  | fallback | activated |
      | de_DE |          | no        |
      | en_US |          | yes       |
      | fr_FR |          | yes       |
    And I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the locales page
    Then I should see the filters Code and Activated
    And the grid should contain 3 elements
    And I should see locales de_DE, en_US and fr_FR

  Scenario: Successfully filter by code
    Given I am on the locales page
    When I filter by "Code" with value "e"
    Then the grid should contain 2 elements
    And I should see locales de_DE and en_US
    And I should not see locales fr_FR

  Scenario: Successfully filter by activated "yes"
    Given I am on the locales page
    When I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
    And I should see locales en_US and fr_FR
    And I should not see locale de_DE

  Scenario: Successfully filter by activated "no"
    Given I am on the locales page
    When I filter by "Activated" with value "no"
    Then the grid should contain 1 element
    And I should see locales de_DE
    And I should not see locale en_US and fr_FR
