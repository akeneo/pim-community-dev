@javascript
Feature: Update product when removing an option of a choice attribute
  In order to keep my data consistent
  As a product manager
  I need products to be updated when removing options of a choice attribute

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully update product when removing an option of a simple select attribute
    Given the following product:
      | sku | manufacturer |
      | foo | Converse     |
    And I am on the "manufacturer" attribute page
    And I visit the "Values" tab
    And I remove the "Converse" option
    And I save the attribute
    When I edit the "foo" product
    Then the product Manufacturer should be empty
    When I visit the "History" tab
    Then I should see history:
      | version | property     | value |
      | 2       | manufacturer |       |

  Scenario: Successfully update product when removing an option of a multi select attribute
    Given the following product:
      | sku | weather_conditions |
      | foo | cold, snowy        |
    And I am on the "weather_conditions" attribute page
    And I visit the "Values" tab
    And I remove the "cold" option
    And I save the attribute
    When I edit the "foo" product
    Then the product Weather conditions should be "Snowy"
    When I visit the "History" tab
    Then I should see history:
      | version | property           | value |
      | 2       | weather_conditions | snowy |

  Scenario: Successfully update product when removing multiple options of a multi select attribute
    Given the following product:
      | sku | weather_conditions    |
      | foo | dry, hot, cold, snowy |
    And I am on the "weather_conditions" attribute page
    And I visit the "Values" tab
    And I remove the "hot" option
    And I wait for options to load
    And I remove the "cold" option
    And I wait for options to load
    And I remove the "snowy" option
    And I wait for options to load
    And I save the attribute
    When I edit the "foo" product
    Then the product Weather conditions should be "Dry"
    When I visit the "History" tab
    Then I should see history:
      | version | property           | value |
      | 2       | weather_conditions | dry   |
