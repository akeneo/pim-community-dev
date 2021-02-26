@javascript
Feature: Catalog updates - Remove a product used by a project
  In order manage my product catalog
  As a user
  I need to remove a product even if it is used by a project


  Background:
    Given the "teamwork_assistant" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | other     | Other       |
    And the following attributes:
      | code        | label-en_US | type                   | localizable | scopable | decimals_allowed | metric_family | default_metric_unit | useable_as_grid_filter | group     | allowed_extensions |
      | sku         | SKU         | pim_catalog_identifier | 0           | 0        |                  |               |                     | 1                      | other     |                    |
      | name        | Name        | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | description | Description | pim_catalog_text       | 1           | 0        |                  |               |                     | 0                      | marketing |                    |
    And the following attribute group accesses:
      | attribute group | user group | access |
      | marketing       | All        | view   |
      | marketing       | All        | edit   |
      | other           | All        | view   |
      | other           | All        | edit   |
    And the following categories:
      | code     | label-en_US | parent  |
      | clothing | Clothing    | default |
    And the following families:
      | code   | label-en_US | attributes           | requirements-ecommerce | requirements-mobile  |
      | tshirt | TShirts     | sku,name,description | sku,name,description   | sku,name,description |
    And the following products:
      | sku                  | family | categories | name-en_US                | description-en_US         |
      | tshirt-the-witcher-3 | tshirt | clothing   | T-Shirt "The Witcher III" | T-Shirt "The Witcher III" |
      | tshirt-skyrim        | tshirt | clothing   | T-Shirt "Skyrim"          | T-Shirt "Skyrim"          |
      | tshirt-lcd           | tshirt | clothing   | T-shirt LCD screen        | T-shirt LCD screen        |
    And I am logged in as "Julia"

  Scenario: A project creator can create a project from this category and then he deletes all products from this one.
    Given I am on the products grid
    When I filter by "family" with operator "in list" and value "TShirts"
    And I open the category tree
    And I filter by "category" with operator "" and value "Clothing"
    And I close the category tree
    Then I should be on the products page
    When I click on the create project button
    And I fill in the following information in the popin:
    | project-label       | Summer collection 2017                 |
    | project-description | My very awesome summer collection 2007 |
    | project-due-date    | 05/12/2117                             |
    And I press the "Save" button
    Then I should be on the products page
    When I switch view selector type to "Projects"
    And I open the view selector
    And I apply the "Summer collection 2017" project
    Then I should be on the products page
    And I select rows tshirt-the-witcher-3
    When I select all entities
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected products and product models? All the product models' children will be also deleted."
    When I confirm the removal
    And I wait for the "delete_products_and_product_models" job to finish
    And I am on the products grid
    Then the grid should contain 0 element
