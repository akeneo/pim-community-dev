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
      | product | label | group   | translatable | scopable |
      | lamp    | Name  | General | yes          | no       |
      | ceiling | Name  | General | yes          | no       |
      | torch   | Name  | General | yes          | no       |
      | lamp    | Color | General | no           | no       |
      | ceiling | Color | General | no           | no       |
      | torch   | Color | General | no           | no       |
      | lamp    | Price | General | no           | no       |
    And I am logged in as "Julia"

  Scenario: Allow editing only common attributes
    Given I am on the products page
    When I mass-edit products lamp, torch and ceiling
    And I choose the "Edit attributes" operation
    Then I should see available attributes Name and Color in group "General"

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

  Scenario: Succesfully update many file values at once

  Scenario: Succesfully update many multi-valued values at once
