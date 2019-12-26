@javascript
Feature: Edit product and filter attributes
  In order to be efficient when enriching products
  As a regular user
  I need to be able to edit a product and choose which attributes are displayed

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Mary"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | boots |
      | Family | Boots |
    And I press the "Save" button in the popin
    And I wait to be on the "boots" product page

  Scenario: Edit the product and show only missing required attributes
    When I filter attributes with "All missing required attributes"
    And I visit the "All" group
    Then I should not see the text "Manufacturer"
    But I should see the text "Name"
    And I should see the text "Weather conditions"
    And I should see the text "Description"
    And I should see the text "Price"
    And I should see the text "Rating"
    And I should see the text "Side view"
    And I should see the text "Size"
    And I should see the text "Color"
    When I filter attributes with "All attributes"
    Then I should see the text "Manufacturer"

  Scenario: Edit the product and show only missing required attributes from an attribute group
    When I filter attributes with "All missing required attributes"
    And I visit the "Media" group
    Then I should not see the text "Top view"
    And I should not see the text "Weather conditions"
    But I should see the text "Side view"

  @critical
  Scenario: Edit the product and show only group missing required attributes by clicking on attribute group header
    And I visit the "Product information" group
    And I click on the "info" required attribute indicator
    Then I should not see the text "Manufacturer"
    And I should not see the text "SKU"
    But I should see the text "Name"
    And I should see the text "Weather conditions"
    And I should see the text "Description"
    And I should not see the text "Price"
    And I should not see the text "Rating"
    And I should not see the text "Side view"
    And I should not see the text "Size"
    And I should not see the text "Color"
    When I filter attributes with "All attributes"
    Then I should see the text "Manufacturer"
    And I should see the text "SKU"
    And I should not see the text "Side view"
    And I should not see the text "Size"
    And I should not see the text "Color"
