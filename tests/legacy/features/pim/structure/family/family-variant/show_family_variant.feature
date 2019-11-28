@javascript
Feature: show family variant
  In order to provide accurate information about a family
  As an administrator
  I need to be able to show a family variant in a family

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully show a family variant with two axes
    Given I am on the "Clothing" family page
    And I visit the "Variants" tab
    And I click on the "Clothing by color and size" row
    Then I should see the text "clothing_color_size"
    And I should see the text "Size (Variant axis)"
    And I should see the text "color (Variant axis)"
    And I should see the text "Variation name"
    And I should see the text "Variant attributes level one"
    And I should see the text "Variant attributes level two"
