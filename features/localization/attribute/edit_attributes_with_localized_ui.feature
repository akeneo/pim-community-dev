@javascript
Feature: Edit attributes with localized ui
  In order to have localized UI
  As a product manager
  I need to update values in my locale

  Background:
    Given the "footwear" catalog configuration

  Scenario: Successfully show validation error in english
    Given I am logged in as "Julia"
    And I edit the "price" attribute
    And I fill in "Max number" with "12456,789"
    When I press the "Save" button
    Then I should see a validation error "This type of value expects the use of . to separate decimals."

  Scenario: Successfully show validation error in french
    Given I am logged in as "Julien"
    And I edit the "price" attribute
    And I fill in "Nombre max" with "12456.789"
    When I press the "Enregistrer" button
    Then I should see a validation error "Ce type de valeur attend , comme séparateur de décimales."

  Scenario: Successfully show no validation error in english
    Given I am logged in as "Julia"
    And I edit the "price" attribute
    And I fill in "Max number" with "12456.789"
    When I press the "Save" button
    Then I should not see a validation error "This type of value expects the use of . to separate decimals."

  Scenario: Successfully show no validation error in french
    Given I am logged in as "Julien"
    And I edit the "price" attribute
    And I fill in "Nombre max" with "12456,789"
    When I press the "Enregistrer" button
    Then I should not see a validation error "Ce type de valeur attend , comme séparateur de décimales."
