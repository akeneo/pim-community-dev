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

  Scenario: Successfully display the sortable columns
    Given I am on the associations page
    Then the rows should be sortable by code and label
    And the rows should be sorted ascending by code
    And I should see sorted associations cross_sell, substitution and up_sell

  Scenario: Successfully sort associations by code
    Given I am on the associations page
    When I sort by "code" value ascending
    Then I should see sorted associations cross_sell, substitution and up_sell
    When I sort by "code" value descending
    Then I should see sorted associations up_sell, substitution and cross_sell

  Scenario: Successfully sort associations by label
    Given I am on the associations page
    When I sort by "label" value ascending
    Then I should see sorted associations substitution, cross_sell and up_sell
    When I sort by "label" value descending
    Then I should see sorted associations up_sell, cross_sell and substitution
