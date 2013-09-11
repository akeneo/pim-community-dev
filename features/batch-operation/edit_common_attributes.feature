@javascript
Feature: Edit common attributes of many products at once
  In order to update the same information on many products
  As Julia
  I need to be able to edit some common attributes of many products at once

  Background:
    Given a "lamp" product
    And a "ceiling" product
    And the following attribute group:
      | name    |
      | General |
    And the following product attributes:
      | product | label | group   | translatable | scopable |
      | lamp    | Name  | General | yes          | yes      |
      | lamp    | Color | General | no           | no       |
      | lamp    | Price | General | no           | no       |
      | ceiling | Name  | General | yes          | yes      |
      | ceiling | Color | General | no           | no       |
    And I am logged in as "Julia"

  Scenario: Allow editing only common attributes
    Given I mass-edit products lamp and ceiling
    When I choose the "Edit attributes" operation
    Then I should see available attributes Name and Color in group "General"

  Scenario: Succesfully update many attributes at once
