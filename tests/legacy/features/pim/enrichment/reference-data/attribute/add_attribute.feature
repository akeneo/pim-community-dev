@javascript
Feature: Add attribute options
  In order to define choices for a choice attribute
  As a product manager

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  @critical
  Scenario: Successfully create a simple reference data
    Given I create a "Reference data simple select" attribute
    And I fill in the following information:
      | Code                | mycolor |
      | Reference data type | color   |
      | Attribute group     | Other   |
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"

  Scenario: Successfully create a multiple reference data
    Given I create a "Reference data multi select" attribute
    And I fill in the following information:
      | Code                | mycolor |
      | Reference data type | fabric  |
      | Attribute group     | Other   |
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"
