@javascript
Feature: Display the attribute history localized values
  In order to have localized UI
  As a product manager
  I need to see localized values in attribute history

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julien"

  Scenario: Successfully show localized numbers
    Given I am on the "price" attribute page
    Then I should see the text "Nombre max"
    When I fill in "Nombre max" with "12456,789"
    And I press the "Enregistrer" button
    When I visit the "Historique" tab
    Then there should be 2 update
    And I should see history:
      | version | author                              | property   | value      |
      | 2       | Julien Février - Julien@example.com | number_max | 12 456,789 |
