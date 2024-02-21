@javascript
Feature: Order attributes
  In order to always have the same attribute fields order in each locale
  As a regular user
  I need to be able to set an order parameter which will manage fields order

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully update an attribute position
    Given I am on the "info" attribute group page
    And I visit the "Attributes" tab
    Then the attribute "SKU" should be in position 1
    And the attribute "Name" should be in position 2
    And the attribute "Manufacturer" should be in position 3
    And the attribute "Weather conditions" should be in position 4
    And the attribute "Description" should be in position 5
    And the attribute "Length" should be in position 6
    And I change the attribute "Description" position to 2
    Then I save the attribute group
    When I am on the "info" attribute group page
    And I visit the "Attributes" tab
    Then the attribute "SKU" should be in position 1
    And the attribute "Description" should be in position 2
    And the attribute "Name" should be in position 3
    And the attribute "Manufacturer" should be in position 4
    And the attribute "Weather conditions" should be in position 5
    And the attribute "Length" should be in position 6

  Scenario: Update an attribute position in the product view
    Given the following product:
      | sku      | description-en_US-mobile | manufacturer | weather_conditions | length        |
      | boot-001 | Nice dark rangers        | Caterpillar  | wet                | 50 CENTIMETER |
    When I am on the "boot-001" product page
    Then the attribute "SKU" should be in position 1
    And the attribute "Manufacturer" should be in position 2
    And the attribute "Weather conditions" should be in position 3
    And the attribute "Description" should be in position 4
    And the attribute "Length" should be in position 5
    When I am on the "info" attribute group page
    And I visit the "Attributes" tab
    And I change the attribute "Description" position to 2
    Then I save the attribute group
    When I am on the "boot-001" product page
    Then the attribute "SKU" should be in position 1
    And the attribute "Description" should be in position 2
    And the attribute "Manufacturer" should be in position 3
    And the attribute "Weather conditions" should be in position 4
    And the attribute "Length" should be in position 5
