@javascript
Feature: Keep context on product assets
  In order to keep context on product assets
  As an asset manager
  I want to keep context when a edit an asset

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Keep locale when editing a localizable asset
    Given I am on the assets grid
    And I switch the locale to "fr_FR"
    And I click on the "chicagoskyline" row
    Then I should be on the "chicagoskyline" asset edit page
    And the locale "fr_FR" should be selected

  Scenario: Keep local when go back to the grid
    Given I am on the assets grid
    And the locale "en_US" should be selected
    And I click on the "chicagoskyline" row
    Then I should be on the "chicagoskyline" asset edit page
    And the locale "en_US" should be selected
    When I switch the locale to "fr_FR"
    And I click on the Akeneo logo
    When I am on the assets grid
    Then the locale "fr_FR" should be selected

  Scenario: Keep locale when editing a non localizable asset
    Given I am on the assets grid
    And I switch the locale to "fr_FR"
    And I click on the "paint" row
    Then I should be on the "paint" asset edit page
    And I click on the Akeneo logo
    When I am on the assets grid
    Then the locale "fr_FR" should be selected
