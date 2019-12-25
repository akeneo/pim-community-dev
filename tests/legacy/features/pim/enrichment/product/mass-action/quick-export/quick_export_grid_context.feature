@javascript
Feature: Quick export products according to the product grid context
  In order to quick export a set of products
  As a product manager
  I need to be able to display products on the product grid and export them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | name-en_US    | price          | size | color | 123 | description-en_US-tablet |
      | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black | aaa | Mob                      |
      | sneakers | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white | bbb | ylette                   |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   | ccc |                          |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | ddd |                          |
    And I am logged in as "Julia"

  Scenario: Successfully quick export products from grid context as a XLSX file
    Given I am on the products grid
    And I display the columns SKU, Name, Label, Family, Color, Complete, Groups, Price, Size, Created at, Updated at, Description and Weight
    And I select rows boots, sneakers, pump
    When I press "Excel (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "xlsx_product_grid_context_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                      |
      | success | Quick export XLSX product quick export grid context finished |
    When I go on the last executed job resume of "xlsx_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And the names of the exported files of "xlsx_product_grid_context_quick_export" should be "1_products_export_grid_context_en_US_tablet.xlsx,2_product_models_export_grid_context_en_US_tablet.xlsx"
    And exported xlsx file 1 of "xlsx_product_grid_context_quick_export" should contain:
      | sku      | color | description-en_US-tablet | family   | groups | name-en_US    | price-EUR | price-USD | size | weight | weight-unit |
      | boots    | black | Mob                      | boots    |        | Amazing boots | 20        | 25        | 40   |        |             |
      | sneakers | white | ylette                   | sneakers |        | Sneakers      | 50        | 60        | 42   |        |             |
      | pump     | blue  |                          |          |        | Pump          | 15        | 20        | 41   |        |             |
