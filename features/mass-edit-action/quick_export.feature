@javascript
Feature: Quick export many products from datagrid
  In order to quick export a set of products
  As Julia
  I need to be able to display products I want and export them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family      |
      | boots    | boots       |
      | sneakers | sneakers    |
      | sandals  | sandals     |
      | pump     |             |
      | highheels | high_heels |
    And I am logged in as "Julia"

  Scenario: Successfully quick export products
    Given I am on the products page
    When I press "CSV (all)" on the "Quick Export" dropdown button 