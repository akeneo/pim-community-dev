@javascript
Feature: Edit common attributes of many products at once with locale specific case
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given the "apparel" catalog configuration
    And the following products:
    | sku    | family  | description-fr_FR-ecommerce |
    | tshirt | tshirts | Mon joli tshirt             |
    And I am logged in as "Julia"
    And I am on the products grid

  @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Allow editing only common attributes, including locale specific attribute
    Given I switch the locale to "de_DE"
    And I select row tshirt
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Kosten, Anzahl auf Lager, Datenblatt, Zollsteuer in group "Intern"

  @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Allow editing only common attributes, excluding locale specific attribute
    Given I switch the locale to "en_US"
    And I select row tshirt
    And I press the "Bulk actions" button
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Cost, Number in stock, Datasheet in group "Internal"
