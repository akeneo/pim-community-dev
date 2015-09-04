@javascript
Feature: Keep context on product assets
  In order to keep context on product assets
  As an asset manager
  I want to keep context when a edit an asset

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Keep locale when editing an asset
    Given I am on the assets page
    And I switch the locale to "French (France)"
    And I click on the "chicagoskyline" row
    Then I should be on the "chicagoskyline" asset edit page
    And the locale "français (France)" should be selected

  Scenario: Keep local when go back to the grid
    Given I am on the assets page
    And the locale "English (United States)" should be selected
    And I click on the "chicagoskyline" row
    Then I should be on the "chicagoskyline" asset edit page
    And the locale "English (United States)" should be selected
    When I switch the locale to "French (France)"
    And I click back to grid
    Then I should be on the assets page
    Then the locale "français (France)" should be selected
