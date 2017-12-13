@javascript
Feature: Ensures the user can filter on the parent attribute of product models
  In order to the search of products and product models
  As a catalog manager
  I should be able to filter on the parent of product models and product.

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "julia"
    Given I am on the products grid

  Scenario: Successfully filters on the parent field
    Given I filter by "parent" with operator "IN LIST" and value "model-hat"
    Then I shou
