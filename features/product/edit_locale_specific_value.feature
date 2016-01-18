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

  @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Display the custom tax on the available locale
    Given I am on the "tshirt" product page
    And I visit the "Internal" group
    And I switch the locale to "de_DE"
    Then I should see "Zollsteuer"

  @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Don't display the custom tax on not available locale
    Given I am on the "tshirt" product page
    And I visit the "Internal" group
    And I switch the locale to "en_US"
    Then I should see the text "This locale specific field is not available in this locale"

  Scenario: Handle the display of a locale specific field even if not localizable
    Given I am on the "tshirt" product page
    And I switch the locale to "en_US"
    And I add available attributes Under European law
    And I visit the "General" group
    Then I should see the text "This locale specific field is not available in this locale"
    And I switch the locale to "fr_FR"
    Then I should see "Sous la loi Européenne"
    And I switch the locale to "de_DE"
    Then I should see "Nach europäischem Recht"
