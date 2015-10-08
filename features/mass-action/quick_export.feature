@javascript
Feature: Quick export many products from datagrid
  In order to quick export a set of products
  As a product manager
  I need to be able to display products I want and export them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku      | family   | categories        | name-en_US    | price          | size | color | 123 |
      | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black | aaa |
      | sneakers | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white | bbb |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   | ccc |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | ddd |
    And I am logged in as "Julia"

  Scenario: Successfully quick export products
    Given I am on the products page
    And I select rows boots, sneakers
    Then I press "CSV (All attributes)" on the "Quick Export" dropdown button
    And I wait for the quick export to finish
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                        |
      | success | Quick export CSV product quick export finished |
    Then I go on the last executed job resume of "csv_product_quick_export"
    And I should see "COMPLETED"
    And the path of the exported file of "csv_product_quick_export" should be "/tmp/products_export_en_US_tablet.csv"
    And exported file of "csv_product_quick_export" should contain:
    """
    sku;123;categories;color;enabled;family;groups;name-en_US;price-EUR;price-USD;size
    boots;aaa;winter_collection;black;1;boots;;"Amazing boots";20.00;25.00;40
    sneakers;bbb;summer_collection;white;1;sneakers;;Sneakers;50.00;60.00;42
    """
