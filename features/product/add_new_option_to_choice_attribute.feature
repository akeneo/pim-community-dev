@javascript
Feature: Add a new option to a choice attribute directly from the product edit form
  In order to easily add a new option to a choice attribute
  As a product manager
  I need to be able to create a new attribute option without leaving the product edit page

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku   | size | weather_conditions |
      | boots | 40   | wet                |
    And I am logged in as "Julia"
    And I am on the "boots" product page

  Scenario: Sucessfully add a new option to a simple select attribute
    Given I visit the "Sizes" group
    And I add a new option to the "Size" attribute
    And I fill in the following information in the popin:
      | Code  | 47       |
      | en_US | 47 (XXL) |
    And I press the "Save" button in the popin
    And I save the product
    Then the product Size should be "47 (XXL)"

  Scenario: Sucessfully add a new option to a multi select attribute
    Given I add a new option to the "Weather conditions" attribute
    When I fill in the following information in the popin:
      | Code  | very_wet      |
      | en_US | Extremely wet |
    And I press the "Save" button in the popin
    And I save the product
    Then the product Weather conditions should be "Wet, Extremely wet"
