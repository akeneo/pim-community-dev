@javascript
Feature: Edit common attributes of many products at once
  In order to update the same information on many products
  As Julia
  I need to be able to edit some common attributes of many products at once

  Background:
    Given the "default" catalog configuration
    And a "lamp" product
    And a "ceiling" product
    And a "torch" product
    And the following attribute group:
      | code      | label     | locale  |
      | general   | General   | english |
      | technical | Technical | english |
      | general   | Général   | french  |
      | technical | Technique | french  |
    And the following product attributes:
      | product | label  | group     | translatable | scopable | type        | metric family | default metric unit | locale | scope |
      | lamp    | Name   | general   | yes          | no       | text        |               |                     | en_US  |       |
      | ceiling | Name   | general   | yes          | no       | text        |               |                     | en_US  |       |
      | torch   | Name   | general   | yes          | no       | text        |               |                     | en_US  |       |
      | lamp    | Colors | technical | no           | no       | multiselect |               |                     |        |       |
      | ceiling | Colors | technical | no           | no       | multiselect |               |                     |        |       |
      | torch   | Colors | technical | no           | no       | multiselect |               |                     |        |       |
      | lamp    | Price  | general   | no           | no       | prices      |               |                     |        |       |
      | torch   | Price  | general   | no           | no       | prices      |               |                     |        |       |
      | ceiling | Visual | general   | no           | no       | image       |               |                     |        |       |
      | torch   | Visual | general   | no           | no       | image       |               |                     |        |       |
      | lamp    | Weight | technical | no           | no       | metric      | Weight        | KILOGRAM            |        |       |
      | torch   | Weight | technical | no           | no       | metric      | Weight        | KILOGRAM            |        |       |
    And the following attribute label translations:
      | attribute | lang    | label    |
      | name      | english | Name     |
      | name      | french  | Nom      |
      | colors    | english | Colors   |
      | colors    | french  | Couleurs |
      | price     | english | Price    |
      | price     | french  | Prix     |
      | visual    | english | Visual   |
      | visual    | french  | Visuel   |
      | weight    | english | Weight   |
      | weight    | french  | Poids    |
    And I am logged in as "Julia"

  Scenario: Allow editing only common attributes
    Given I am on the products page
    When I mass-edit products lamp, torch and ceiling
    And I choose the "Edit attributes" operation
    Then I should see available attribute Name in group "General"
    And I should see available attribute Colors in group "Technical"

  Scenario: Succesfully update many text values at once
    Given I am on the products page
    When I mass-edit products lamp, torch and ceiling
    And I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "Lamp"
    And I move on to the next step
    Then the english name of "lamp" should be "Lamp"
    And the english name of "torch" should be "Lamp"
    And the english name of "ceiling" should be "Lamp"

  Scenario: Succesfully update many price values at once
    Given I am on the products page
    When I mass-edit products lamp and torch
    And I choose the "Edit attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "100"
    And I change the "€ Price" to "150"
    And I move on to the next step
    Then the prices "Price" of products lamp and torch should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  Scenario: Succesfully update many file values at once
    Given I am on the products page
    When I mass-edit products torch and ceiling
    And I choose the "Edit attributes" operation
    And I display the Visual attribute
    And I attach file "akeneo.jpg" to "Visual"
    And I move on to the next step
    Then the file "Visual" of products torch and ceiling should be "akeneo.jpg"

  Scenario: Succesfully update many multi-valued values at once
    Given the following "Colors" attribute options: Red, Blue and White
    Given I am on the products page
    When I mass-edit products lamp and ceiling
    And I choose the "Edit attributes" operation
    And I display the Colors attribute
    And I change the "Colors" to "Red, Blue"
    And I move on to the next step
    Then the options "Colors" of products lamp and ceiling should be:
      | value |
      | Red   |
      | Blue  |

  Scenario: Succesfully update many metric values at once
    Given I am on the products page
    When I mass-edit products lamp and torch
    And I choose the "Edit attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I move on to the next step
    Then the metric "Weight" of products lamp and torch should be "600"

  Scenario: Succesfully translate in english groups and labels
    Given I am on the products page
    When I mass-edit products lamp and torch
    And I choose the "Edit attributes" operation
    And I display the Name and Colors attributes
    Then I should see "Technical"
    And I should see "General"
    And I should see "Name"
    And I should see "Colors"

  Scenario: Succesfully translate in french groups and labels
    Given I am on the products page
    When I mass-edit products lamp and torch
    And I choose the "Edit attributes" operation
    And I switch the locale to "French (France)"
    And I display the Nom and Couleur attributes
    Then I should see "Technique"
    And I should see "Général"
    And I should see "Nom"
    And I should see "Couleurs"
