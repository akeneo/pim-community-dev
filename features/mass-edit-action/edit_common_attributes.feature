@javascript
Feature: Edit common attributes of many products at once
  In order to update the same information on many products
  As Julia
  I need to be able to edit some common attributes of many products at once

  Background:
    Given a "lamp" product
    And a "ceiling" product
    And a "torch" product
    And the following attribute group:
      | name    |
      | General |
    And the following product attributes:
      | product | label  | group   | translatable | scopable | type        |
      | lamp    | Name   | General | yes          | no       | text        |
      | ceiling | Name   | General | yes          | no       | text        |
      | torch   | Name   | General | yes          | no       | text        |
      | lamp    | Colors | General | no           | no       | multiselect |
      | ceiling | Colors | General | no           | no       | multiselect |
      | torch   | Colors | General | no           | no       | multiselect |
      | lamp    | Price  | General | no           | no       | prices      |
      | torch   | Price  | General | no           | no       | prices      |
    And I am logged in as "Julia"

  Scenario: Allow editing only common attributes
    Given I am on the products page
    When I mass-edit products lamp, torch and ceiling
    And I choose the "Edit attributes" operation
    Then I should see available attributes Name and Colors in group "General"

  Scenario: Succesfully update many text values at once
    Given I am on the products page
    When I mass-edit products lamp, torch and ceiling
    And I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "Lamp"
    And I move on to the next step
    Then I should see "Product(s) attribute(s) have been updated"
    And the english name of lamp should be "Lamp"
    And the english name of torch should be "Lamp"
    And the english name of ceiling should be "Lamp"

  Scenario: Succesfully update many price values at once
    Given the following currencies:
      | code | activated |
      | USD  | yes       |
      | EUR  | yes       |
    And I am on the products page
    When I mass-edit products lamp and torch
    And I choose the "Edit attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "100"
    And I change the "€ Price" to "150"
    And I move on to the next step
    Then I should see "Product(s) attribute(s) have been updated"
    And the prices "Price" of products lamp and torch should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  Scenario: Succesfully update many file values at once

  Scenario: Succesfully update many multi-valued values at once
    Given the following "Colors" attribute options: Red, Blue and White
    Given I am on the products page
    When I mass-edit products lamp and ceiling
    And I choose the "Edit attributes" operation
    And I display the Colors attribute
    And I change the "Colors" to "Red, Blue"
    And I move on to the next step
    Then I should see "Product(s) attribute(s) have been updated"
    And the options "Colors" of products lamp and ceiling should be:
      | value |
      | Red   |
      | Blue  |
