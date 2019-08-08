@javascript
Feature: Navigate to product model and product edit pages
  In order to enrich the catalog
  As a regular user
  I need to be able to navigate to the product model and product edit pages

  @critical
  Scenario: Successfully navigate to a product model and a product edit page
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I type "color" in the manage filter input
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    When I click on the "tshirt-unique-size-crimson-red" row
    Then I should see the title "Product tshirt-unique-size-crimson-red | Edit"
    When I am on the products grid
    And I click on the "model-tshirt-divided-crimson-red" row
    Then I should see the title "Product model Divided crimson red | Edit"
