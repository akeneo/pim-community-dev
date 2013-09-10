@javascript
Feature: Edit common attributes of many products at once
  In order to update the same information on many products
  As Julia
  I need to be able to edit some common attributes of many products at once

  Scenario: Allow editing only common attributes
    Given a "lamp" product
    And a "ceiling" product
    And the following attribute group:
      | name    |
      | General |
    And the following product attribute:
      | product | label | group   |
      | lamp    | Name  | General |
      | lamp    | Color | General |
      | lamp    | Price | General |
      | ceiling | Name  | General |
      | ceiling | Color | General |
    And I am logged in as "Julia"
    When I mass-edit products lamp and ceiling
    And I choose the "Edit attributes" operation
    Then I should see available attributes Name and Color in group "General"

  Scenario: Display only channels for which the current locale is activated

  Scenario: Succesfully update many attributes at once
