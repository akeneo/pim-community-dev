@javascript
Feature: Datagrid views
  In order to easily manage different views in the datagrid
  As a regular user
  I need to be able to access datagrid views

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Mary"

  Scenario: Do not display view from which an attribute is not accessible
      Given the following datagrid views:
      | label          | alias        | columns         | filters   |
      | Granted view   | product-grid | sku             | f[sku]=-1 |
      | Forbidden view | product-grid | sku, main_color | f[sku]=-1 |
      And I am on the products grid
      Then I should see the "Default view" view
      And I should see the "Granted view" view
      And I should not see the "Forbidden view" view

  Scenario: Do not display view from which a filter is not accessible
    Given the following datagrid views:
      | label          | alias        | columns | filters                  |
      | Granted view   | product-grid | sku     | f[sku]=-1                |
      | Forbidden view | product-grid | sku     | f[main_color][value]=red |
    And I am on the products grid
    Then I should see the "Default view" view
    And I should see the "Granted view" view
    And I should not see the "Forbidden view" view

  Scenario: Do not display view from which a category filter is not accessible
    Given the following datagrid views:
      | label          | alias        | columns | filters                            |
      | Granted view   | product-grid | sku     | f[sku]=-1                          |
      | Forbidden view | product-grid | sku     | f[category][value][categoryId]=999 |
    And I am on the products grid
    Then I should see the "Default view" view
    And I should see the "Granted view" view
    And I should not see the "Forbidden view" view
