@javascript
Feature: Show a product product model
  In order to consult the catalog
  As a product manager
  I need to be able view a product model I can't edit

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Mary"
    And I am on the "master_men_blazers_deals" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I save the category
    And I am on the "supplier_mongo" category page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view products | Redactor |
      | Allowed to edit products |          |
      | Allowed to own products  |          |
    And I save the category

  @critical
  Scenario: Seeing the view actions on the product grid
    Given I am on the products grid
    When I open the category tree
    And I select the "Master" tree
    And I expand the "Men" category
    And I expand the "Blazers" category
    And I filter by "category" with operator "" and value "Deals"
    And I close the category tree
    Then I should be able to view the "View the product" action of the row which contains "caelus"
    And I should not be able to view the "Edit attributes of the product" action of the row which contains "caelus"
    And I should not be able to view the "Classify the product" action of the row which contains "caelus"
    And I should not be able to view the "Delete the product" action of the row which contains "caelus"

  @critical
  Scenario: View a product model in read only mode
    Given I edit the "caelus" product model
    When I visit the "Erp" group
    Then the product Supplier should be "mongo"
    And the field Supplier should be read only
    And the product Price in EUR should be "999"
    And the field Price should be read only
    When I visit the "Marketing" group
    Then the product Model name should be "Tuxedo with animal print"
    And the field Model name should be read only
