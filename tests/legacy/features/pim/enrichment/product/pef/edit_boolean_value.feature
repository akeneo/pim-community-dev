@javascript
Feature: Edit a boolean value
  In order to enrich the catalog
  As a regular user
  I need to be able edit boolean values of a product

  Scenario: Successfully update a boolean value
    Given the "apparel" catalog configuration
    And I am logged in as "Mary"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | gladiator |
      | Family | T-shirts  |
    And I press the "Save" button in the popin
    And I wait to be on the "gladiator" product page
    And I visit the "Additional information" group
    When I check the "Handmade" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And attribute handmade of "gladiator" should be "true"
    When I uncheck the "Handmade" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And attribute handmade of "gladiator" should be "false"
