@javascript
Feature: list family variant
  In order to provide accurate information about a family
  As an administrator
  I need to be able to list family variant in a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully list the families
    Given I am on the "Clothing" family page
    And I visit the "Variants" tab
    Then the grid should contain 5 elements
    And I should see the text "Clothing by material and size"
    And I should see the text "Color, Size"
    And I should see the text "Variant axis level 1"
    Then I search "Clothing by color"
    Then the grid should contain 3 elements
    And I should not see the text "Clothing by material and size"
    And I should see the text "Color, Size"
