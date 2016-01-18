@javascript
Feature: Add attribute options
  In order to define choices for a choice attribute
  As a product manager

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully create a simple reference data
    Given I create a "Reference data simple select" attribute
    Given I fill in the following information:
      | Code                | mycolor   |
      | Reference data name | color     |
      | Attribute group     | Other     |
    Then I save the attribute
    Then I should see flash message "Attribute successfully created"

  Scenario: Successfully create a multiple reference data
    Given I create a "Reference data multi select" attribute
    Given I fill in the following information:
      | Code                | mycolor   |
      | Reference data name | fabric    |
      | Attribute group     | Other     |
    Then I save the attribute
    Then I should see flash message "Attribute successfully created"
