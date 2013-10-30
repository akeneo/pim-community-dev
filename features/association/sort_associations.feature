@javascript
Feature: Sort associations
  In order to easily manage associations in the catalog
  As a user
  I need to be able to sort associations by several columns

  Background:
    Given the following associations:
      | code         | label |
      | cross_sell   | B     |
      | up_sell      | C     |
      | substitution | A     |
    And I am logged in as "admin"

  Scenario: Successfully sort the associations in the grid
    Given I am on the associations page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
