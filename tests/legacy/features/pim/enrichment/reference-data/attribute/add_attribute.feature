@javascript @reference-data-feature-enabled
Feature: Add attribute options
  In order to define choices for a choice attribute
  As a product manager

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  @critical
  Scenario: Successfully create a simple reference data
    Given I create a "Reference data simple select" attribute with code "mycolor"
    And I fill in the following information:
      | Attribute group     | Other   |
      | Reference data type | color   |
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"

  Scenario: Successfully create a multiple reference data
    Given I create a "Reference data multi select" attribute with code "mycolor"
    And I fill in the following information:
      | Attribute group     | Other   |
      | Reference data type | fabric  |
    When I save the attribute
    Then I should see the flash message "Attribute successfully created"
