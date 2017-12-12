@javascript
Feature: Edit attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to Edit attributes of many products at once

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
    And I am on the products grid
    And I switch the scope to "Mobile"
    And I switch the locale to "fr_FR"
    When I select rows boots and sandals
    And I press the "Bulk actions" button
    And I choose the "Edit attributes" operation
    And I display the Nom and Taille attributes
    Then I should see the text "[info]"
    And I should see the text "Nom"
    When I visit the "[sizes]" group
    Then I should see the text "Taille"
