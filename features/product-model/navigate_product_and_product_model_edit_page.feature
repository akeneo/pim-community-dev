@javascript
Feature: Navigate to product model and product edit pages
  In order to enrich the catalog
  As a regular user
  I need to be able to navigate to the product model and product edit pages

  @ce
  Scenario: Successfully navigate to a product model and a product edit page
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    When I click on the "tshirt-unique-size-crimson-red" row
    Then I should see the title "Products tshirt-unique-size-crimson-red | Edit"
    When I am on the products page
    And I click on the "model-tshirt-divided-crimson-red" row
    Then I should see the title "Product models Divided crimson red | Edit"
