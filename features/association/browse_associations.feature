@javascript
Feature: Browse associations
  In order to list the existing associations in the catalog
  As a user
  I need to be able to see associations

  Background:
    Given the following associations:
      | code         | label        |
      | cross_sell   | Cross sell   |
      | up_sell      | Upsell       |
      | substitution | Substitution |
    And I am logged in as "admin"

  Scenario: Successfully display associations
    Given I am on the associations page
    Then the grid should contain 3 elements
    And I should see the columns Code and Label
    And I should see associations cross_sell, up_sell and substitution
