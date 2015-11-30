@javascript
Feature: Edit attributes with localized ui
  In order to have localized UI
  As a product manager
  I need to update values in my locale

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | code | label-fr_FR | type   | group | date_min   |
      | date | Date        | date   | other | 2016-01-31 |

  Scenario: Successfully show validation error for a number attribute in english
    Given I am logged in as "Julia"
    And I edit the "price" attribute
    And I fill in "Max number" with "12456,789"
    When I press the "Save" button
    Then I should see a validation error "This type of value expects the use of . to separate decimals."

  Scenario: Successfully show validation error for a number attribute in french
    Given I am logged in as "Julien"
    And I edit the "price" attribute
    And I fill in "Nombre max" with "12456.789"
    When I press the "Enregistrer" button
    Then I should see a validation error "Ce type de valeur attend , comme séparateur de décimales."

  Scenario: Successfully save localized english number
    Given I am logged in as "Julia"
    And I edit the "price" attribute
    And I fill in "Max number" with "12456.789"
    When I press the "Save" button
    Then I should not see a validation error "This type of value expects the use of . to separate decimals."
    And the field Max number should contain "12456.7890"

  Scenario: Successfully save localized french number
    Given I am logged in as "Julien"
    And I edit the "price" attribute
    And I fill in "Nombre max" with "12456,789"
    When I press the "Enregistrer" button
    Then I should not see a validation error "Ce type de valeur attend , comme séparateur de décimales."
    And the field Nombre max should contain "12456,789"

  Scenario: Successfully save localized english date
    Given I am logged in as "Julia"
    When I edit the "date" attribute
    Then the field Min date should contain "01/31/2016"
    And I fill in "Min date" with "12/31/2015"
    And I press the "Save" button
    Then the field Min date should contain "12/31/2015"

  Scenario: Successfully save localized french date
    Given I am logged in as "Julien"
    When I edit the "date" attribute
    Then the field Date mini should contain "31/01/2016"
    And I fill in "Date mini" with "31/12/2015"
    And I press the "Enregistrer" button
    Then the field Date mini should contain "31/12/2015"
