@javascript
Feature: Sort currencies
  In order to sort currencies in the catalog
  As a user
  I need to be able to sort currencies by several columns in the catalog

  Background:
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
      | GBP  | no        |
    And I am logged in as "admin"

  Scenario: Successfully sort currencies by code ascending
    Given I am on the currencies page
    Then I should see sorted currencies EUR, GBP and USD
    When I sort by "code" value ascending
    Then I should see sorted currencies EUR, GBP and USD

  Scenario: Successfully sort currencies by code descending
    Given I am on the currencies page
    Then I should see sorted currencies EUR, GBP and USD
    When I sort by "code" value descending
    Then I should see sorted currencies USD, GBP and EUR

  Scenario: Successfully sort currencies by activated ascending
    Given I am on the currencies page
    Then I should see sorted currencies EUR, GBP and USD
    When I sort by "Activated" value ascending
    Then I should see sorted currencies USD, EUR and GBP

  Scenario: Successfully sort currencies by activated descending
    Given I am on the currencies page
    Then I should see sorted currencies EUR, GBP and USD
    When I sort by "Activated" value descending
    Then I should see sorted currencies GBP, USD and EUR
