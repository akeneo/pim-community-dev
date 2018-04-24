@javascript
Feature: Show system information
  In order to inform
  As an administrator
  I need to be able to show system information

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully display product model information
    Given I am on the System info page
    Then I should see the text "Product models 80"
    And I should see the text "Variant products 236"
    And I should see the text "Family variants 8"
