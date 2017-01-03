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
      | shoes | 40   | wet                |
    And I am logged in as "Julia"
    And I am on the "boots" product page

  @unstable
  Scenario: Successfully add a new option to a simple select attribute
    Given I visit the "Sizes" group
    And I add a new option to the "Size" attribute:
      | Code | 47xxl    |
      | en   | 47 (XXL) |
    And I save the product
    Then the product Size should be "47xxl"

  Scenario: Successfully add a new option to a multi select attribute
    Given I add a new option to the "Weather conditions" attribute:
      | Code | very_wet      |
      | en   | Extremely wet |
    And I save the product
    Then the product Weather conditions should be "very_wet, wet"

  @jira https://akeneo.atlassian.net/browse/PIM-4737
  Scenario: Successfully find a created option in a multiselect attribute through several products
    Given I add a new option to the "Weather conditions" attribute:
      | Code | very_wet      |
      | en   | Extremely wet |
    And I save and back to the grid
    And I am on the "shoes" product page
    And I should be on the product "shoes" edit page
    And I change the "Weather conditions" to "Extremely wet"
