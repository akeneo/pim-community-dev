@javascript
Feature: Quick export many products from datagrid
  In order to quick export a set of products
  As a product manager
  I need to be able to display products I want and export them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | name-en_US    | price          | size | color |
      | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black |
      | sneakers | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  |
    And I am logged in as "Julia"

  @skip
  Scenario: Successfully quick export products
    Given I am on the products page
    Then I select products boots and sneakers
    When I press "CSV (all)" on the "Quick Export" dropdown button
    And I choose to download the file
