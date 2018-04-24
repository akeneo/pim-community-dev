@javascript
Feature: Sort product assets
  In order to easily manage product assets
  As an asset manager
  I need to be able to sort product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"
    And I am on the assets grid

  Scenario: Successfully sort product assets
    And I should be able to naturally sort the rows by Code, Description, End of use, Created at and Last updated at
