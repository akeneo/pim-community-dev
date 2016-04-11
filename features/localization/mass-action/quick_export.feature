@javascript
Feature: Quick export many products with localized attributes from datagrid
  In order to quick export a set of products
  As a product manager
  I need to be able to display products I want and export them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | name-en_US    | price                | size | color | weight      | rate_sale | destocking_date |
      | boots    | boots    | winter_collection | Amazing boots | 20.80 EUR, 25.35 USD | 40   | black | 250 GRAM    | 75.5      | 1999-12-28      |
      | sneakers | sneakers | summer_collection | Sneakers      | 50.00 EUR, 60.00 USD | 42   | white | 125.50 GRAM | 75.00     |                 |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD         | 40   | red   | 0.5 GRAM    | 75        |                 |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD       | 41   | blue  |             |           |                 |
    And I am logged in as "Julien"

  Scenario: Successfully quick export CSV products with localized attributes
    Given I am on the products page
    When I select rows boots, sneakers, sandals and pump
    And I press "CSV (tous les attributs)" on the "Export rapide" dropdown button
    And I wait for the "csv_product_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                  |
      | success | Exportation rapide CSV product quick export est terminée |
    When I go on the last executed job resume of "csv_product_quick_export"
    Then I should see the text "TERMINÉ"
    And the name of the exported file of "csv_product_quick_export" should be "products_export_en_US_mobile.csv"
    And exported file of "csv_product_quick_export" should contain:
    """
    sku;categories;color;description-en_US-mobile;destocking_date;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rate_sale;rating;side_view;size;top_view;weather_conditions;weight;weight-unit
    boots;winter_collection;black;;28/12/1999;1;boots;;;;"Amazing boots";20,80;25,35;75,50;;;40;;;250;GRAM
    sneakers;summer_collection;white;;;1;sneakers;;;;Sneakers;50;60;75;;;42;;;125,50;GRAM
    sandals;summer_collection;red;;;1;sandals;;;;Sandals;5;5;75;;;40;;;0,50;GRAM
    pump;summer_collection;blue;;;1;;;;;Pump;15;20;;;;41;;;;
    """

  Scenario: Successfully quick export XLSX products with localized attributes
    Given I am on the products page
    When I select rows boots, sneakers, sandals and pump
    And I press "XLSX (tous les attributs)" on the "Export rapide" dropdown button
    And I wait for the "xlsx_product_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                   |
      | success | Exportation rapide XLSX product quick export est terminée |
    When I go on the last executed job resume of "xlsx_product_quick_export"
    Then I should see "TERMINÉ"
    And the name of the exported file of "xlsx_product_quick_export" should be "products_export_en_US_mobile.xlsx"
    And exported xlsx file of "xlsx_product_quick_export" should contain:
      | sku      | categories        | color | description-en_US-mobile | destocking_date | enabled | family   | groups | lace_color | manufacturer | name-en_US    | price-EUR | price-USD | rate_sale | rating | side_view | size | top_view | weather_conditions | weight | weight-unit |
      | boots    | winter_collection | black |                          | 28/12/1999      | 1       | boots    |        |            |              | Amazing boots | 20,80     | 25,35     | 75,50     |        |           | 40   |          |                    | 250    | GRAM        |
      | sneakers | summer_collection | white |                          |                 | 1       | sneakers |        |            |              | Sneakers      | 50        | 60        | 75        |        |           | 42   |          |                    | 125,50 | GRAM        |
      | sandals  | summer_collection | red   |                          |                 | 1       | sandals  |        |            |              | Sandals       | 5         | 5         | 75        |        |           | 40   |          |                    | 0,50   | GRAM        |
      | pump     | summer_collection | blue  |                          |                 | 1       |          |        |            |              | Pump          | 15        | 20        |           |        |           | 41   |          |                    |        |             |
