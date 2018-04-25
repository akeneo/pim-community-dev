@javascript
Feature: Browse jobs
  In order to view the list of jobs
  As a product manager
  I need to be able to view a list of job profiles

  Background:
    Given a "clothing" catalog configuration

  Scenario: Successfully view job profiles depending on given permissions for manager
    Given I am logged in as "Julia"
    And I am on the exports page
    Then I should see export profiles Clothing attribute export
    When I am on the imports page
    Then I should see import profiles Clothing product import

  Scenario: Successfully view job profiles depending on given permissions for administrator
    Given I am logged in as "Peter"
    And I am on the exports page
    Then I should see export profiles Clothing attribute export, Clothing option export
    When I am on the imports page
    Then I should see import profiles Clothing product import, Clothing asset category import
