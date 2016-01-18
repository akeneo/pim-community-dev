@javascript
Feature: Edit a localizable value
  In order to enrich the catalog
  As a regular user
  I need to be able to edit a localizable value only when editing in available locale

  Background:
    Given the "apparel" catalog configuration
    And the following products:
      | sku    | family  |
      | tshirt | tshirts |
    And I am logged in as "Mary"
    And I am on the "tshirt" product page

  @jira https://akeneo.atlassian.net/browse/PIM-4536
  Scenario: Handle the display of a localizable field if not locale not in channel
    And I switch the locale to "de_DE"
    And I switch the scope to "tablet"
    Then I should see the text "This localizable field is not available for locale 'de_DE' and channel 'tablet'"
