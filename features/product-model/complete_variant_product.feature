@javascript
Feature: Complete variant product
  In order to the remaining tasks I have to do
  As a regular contributor
  I need to see the number of incomplete variant products

  Background:
    Given a "catalog_modeling" catalog configuration

  Scenario: Try to display the complete variant product for a product model without child
    When I am logged in as "Mary"
    And I am on the products page
    And I show the filter "Model name"
    And I filter by "Model name" with operator "contains" and value "minerva"

  Scenario: Try to display the complete variant product for a product
    When I am logged in as "Mary"
    And I show the filter "Model name"
    When I filter by "Model name" with operator "contains" and value "Bag"
    Then I should not see the text "N/A"

  Scenario: Display the complete variant product for a product model
    When I am logged in as "Mary"
    And I show the filter "Model name"
    When I filter by "Model name" with operator "contains" and value "elegance"
    Then I should not see the text "0/3"
