@javascript
Feature: Edit a locale specific value
  In order to enrich the catalog
  As a regular user
  I need to be able to edit a locale specific value only when editing in available locale

  Background:
    Given the "apparel" catalog configuration
    And the following products:
    | sku    | family  |
    | tshirt | tshirts |
    And I am logged in as "Mary"
    And I am on the "tshirt" product page

  # @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Display the custom tax on the available locale
    And I switch the locale to "de_DE"
    And I visit the "Intern" group
    Then I should see "Zollsteuer"

  # @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Don't display the custom tax on not available locale
    And I switch the locale to "en_US"
    And I visit the "Internal" group
    Then I should not see "Customs tax"
