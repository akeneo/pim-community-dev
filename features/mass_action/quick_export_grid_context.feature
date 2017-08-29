@javascript
Feature: Quick export products according to the product grid context
  In order to quick export a set of products
  As a product manager
  I need to be able to display published products on the product grid and export them

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | label-en_US | type               | useable_as_grid_filter | metric_family | default_metric_unit | decimals_allowed | group | code   |
      | Weight      | pim_catalog_metric | 1                      | Weight        | GRAM                | 1                | other | weight |
    And the following published products:
      | sku      | family   | categories        | name-en_US    | price          | size | color | 123 | description-en_US-tablet | weight | weight-unit |
      | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black | aaa | Mob                      | 20     | GRAM        |
      | sneakers | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white | bbb | ylette                   | 4      | GRAM        |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   | ccc |                          |        |             |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | ddd |                          |        |             |
    And I am logged in as "Julia"

  Scenario: Successfully quick export published products from grid context as a CSV file
    Given I am on the published products grid
    And I display in the published products grid the columns SKU, Name, Label, Family, Color, Complete, Groups, Price, Size, Created at, Updated at, Description and Weight
    And I select rows boots, sneakers, pump
    When I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_published_product_grid_context_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                               |
      | success | Quick export CSV published product quick export grid context finished |
    When I go on the last executed job resume of "csv_published_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And the name of the exported file of "csv_published_product_grid_context_quick_export" should be "published_products_export_grid_context_en_US_tablet.csv"
    And exported file of "csv_published_product_grid_context_quick_export" should contain:
    """
    sku;color;family;groups;name-en_US;price-EUR;price-USD;size;description-en_US-tablet;weight;weight-unit
    boots;black;boots;;Amazing boots;20;25;40;Mob;20;GRAM
    sneakers;white;sneakers;;Sneakers;50;60;42;ylette;4;GRAM
    pump;blue;Pump;;;15;20;41;;;
    """

  Scenario: Successfully quick export products from grid context as a XSLX file
    Given I am on the published products grid
    And I display in the published products grid the columns SKU, Name, Label, Family, Color, Complete, Groups, Price, Size, Created at, Updated at, Description and Weight
    And I select rows boots, sneakers, pump
    When I press "Excel (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "xlsx_published_product_grid_context_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                |
      | success | Quick export XLSX published product quick export grid context finished |
    When I go on the last executed job resume of "xlsx_published_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And the name of the exported file of "xlsx_published_product_grid_context_quick_export" should be "published_products_export_grid_context_en_US_tablet.xlsx"
    And exported xlsx file of "xlsx_published_product_grid_context_quick_export" should contain:
      | sku      | color | family   | groups | name-en_US    | price-EUR | price-USD | size | description-en_US-tablet | weight | weight-unit |
      | boots    | black | boots    |        | Amazing boots | 20        | 25        | 40   | Mob                      | 20     | GRAM        |
      | sneakers | white | sneakers |        | Sneakers      | 50        | 60        | 42   | ylette                   | 4      | GRAM        |
      | pump     | blue  |          |        | Pump          | 15        | 20        | 41   |                          |        |             |
