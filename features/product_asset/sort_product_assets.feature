@javascript
Feature: Sort product assets
  In order to easily manage product assets
  As a product manager
  I need to be able to sort product assets by several columns

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the assets page

  Scenario: Successfully sort product assets
    And I should be able to sort the rows by Code, Description, End of use, Created at and Last updated at
