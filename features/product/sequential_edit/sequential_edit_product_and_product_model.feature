@javascript
Feature: Edit sequentially some products
  In order to enrich the catalog
  As a regular user
  I need to be able to edit sequentially some products

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page

  # PIM-6360: Those tests will be refactored once the sequential edit for product models works.
  Scenario: Successfully sequentially edit some products but not the product models 1/2
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I sort by "SKU" value ascending
    And I select rows model-tshirt-divided-crimson-red, running-shoes-xxs-crimson-red
    When I press "Edit products sequentially" on the "Bulk Actions" dropdown button
    Then I should see the text "running-shoes-xxs-crimson-red"
    And I should see the text "Save and finish"

  Scenario: Successfully sequentially edit some products but not the product models 2/2
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I sort by "SKU" value descending
    And I select rows model-tshirt-divided-crimson-red, running-shoes-xxs-crimson-red
    When I press "Edit products sequentially" on the "Bulk Actions" dropdown button
    Then I should see the text "running-shoes-xxs-crimson-red"
    And I should see the text "Save and finish"
