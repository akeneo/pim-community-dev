@javascript
Feature: Complete variant product
  In order to know the remaining tasks I have to do
  As a regular contributor
  I need to see the number of incomplete variant products

  Background:
    Given a "catalog_modeling" catalog configuration

  Scenario: Update the complete variant product when I change the locale and the channel
    When I am logged in as "Mary"
    And I edit the "model-braided-hat" product model
    Then I should see the text "2 / 2 variant products"
    When I switch the locale to "fr_FR"
    Then I should see the text "1 / 2 variant product"
    When I switch the scope to "mobile"
    And I switch the locale to "de_DE"
    Then I should see the text "0 / 2 variant product"

  Scenario: Try to display the complete variant product for a product model without child
    When I am logged in as "Mary"
    And I am on the products grid
    And I show the filter "name"
    And I filter by "name" with operator "contains" and value "minerva"
    Then I should see the text "0 / 0"

  Scenario: Try to display the complete variant product for a product
    When I am logged in as "Mary"
    And I am on the products grid
    And I show the filter "name"
    And I filter by "name" with operator "contains" and value "Bag"
    Then I should see the text "N/A"

  Scenario: Display the complete variant product for a product model
    When I am logged in as "Mary"
    And I am on the products grid
    And I show the filter "name"
    And I filter by "name" with operator "contains" and value "elegance"
    Then I should see the text "0 / 3"
