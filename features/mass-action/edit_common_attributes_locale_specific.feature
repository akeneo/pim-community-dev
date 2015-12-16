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
    And I am on the products page

  @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Allow editing only common attributes, including locale specific attribute
    Given I mass-edit products tshirt
    And I choose the "Edit common attributes" operation
    And I switch the locale to "de_DE"
    Then I should see available attributes Kosten, Anzahl auf Lager, Datenblatt, Zollsteuer in group "Intern"

  @jira https://akeneo.atlassian.net/browse/PIM-3298
  Scenario: Allow editing only common attributes, excluding locale specific attribute
    Given I mass-edit products tshirt
    And I choose the "Edit common attributes" operation
    And I switch the locale to "en_US"
    Then I should see available attributes Cost, Number in stock, Datasheet in group "Internal"

  Scenario: Successfully mass edit localized product values
    Given I mass-edit products tshirt
    And I choose the "Edit common attributes" operation
    And I switch the locale to "en_US"
    And I display the Description attribute
    And I display the Weight attribute
    And I visit the "General" group
    And I change the Description to "Bar tablet"
    And I visit the "Additional information" group
    And I change the Weight to "10 KILOGRAM"
    And I switch the scope to "ecommerce"
    And I visit the "General" group
    And I change the Description to "Bar ecommerce"
    And I switch the locale to "fr_FR"
    And I switch the locale to "en_US"
    Then the product Description should be empty
    When I switch the scope to "ecommerce"
    Then the product Description should be empty
    Given I visit the "Additional information" group
    Then the product Weight should be "10 KILOGRAM"
    When I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "tshirt" should have the following values:
      | description-en_US-tablet    |                  |
      | description-en_US-ecommerce |                  |
      | description-fr_FR-ecommerce | Mon joli tshirt  |
      | weight                      | 10.0000 KILOGRAM |
