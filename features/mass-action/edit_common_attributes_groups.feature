@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | family  | color | groups |
      | boots   | boots   |       |        |
      | sandals | sandals |       |        |
    And I am logged in as "Julia"

  Scenario: Successfully translate groups and labels
    Given I add the "french" locale to the "mobile" channel
    And the following attribute label translations:
      | attribute | locale | label  |
      | name      | french | Nom    |
      | size      | french | Taille |
    And I am on the products page
    And I filter by "scope" with operator "equals" and value "Mobile"
    And I switch the locale to "fr_FR"
    When I select rows boots and sandals
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Nom and Taille attributes
    Then I should see "[info]"
    And I should see "Nom"
    When I visit the "[sizes]" group
    Then I should see "Taille"
