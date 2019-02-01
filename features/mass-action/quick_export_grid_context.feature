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

  Scenario: Successfully quick export products from grid context as a CSV file
    Given I am on the products grid
    And I display the columns SKU, Name, Label, Family, Color, Complete, Groups, Price, Size, Created at and Updated at, Description and Weight
    And I select rows boots, sneakers, pump
    When I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                     |
      | success | Quick export CSV product quick export grid context finished |
    When I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And the names of the exported files of "csv_product_grid_context_quick_export" should be "1_products_export_grid_context_en_US_tablet.csv,2_product_models_export_grid_context_en_US_tablet.csv"
    And first exported file of "csv_product_grid_context_quick_export" should contain:
    """
    sku;color;description-en_US-tablet;family;groups;name-en_US;price-EUR;price-USD;size
    boots;black;Mob;boots;;Amazing boots;20;25;40
    sneakers;white;ylette;sneakers;;Sneakers;50;60;42
    pump;blue;;;;Pump;15;20;41
    """

  @jira https://akeneo.atlassian.net/browse/PIM-7911
  Scenario: Successfully quick export only current working locale from grid context as a CSV file
    Given I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And I am on the products page
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | blue-suede-shoes |
      | Family | Sneakers         |
    And I press the "Save" button in the popin
    And I wait to be on the "blue-suede-shoes" product page
    And I fill in the following information:
      | Description | Blue suede shoes |
    And I switch the locale to "fr_FR"
    And I fill in the following information:
      | [description] | Chaussures en suedine bleues |
    And I press the "Save" button
    And I should not see the text "There are unsaved changes."
    And I am on the products page
    And I switch the locale to "fr_FR"
    And I display the columns [sku], [description]
    And I select row blue-suede-shoes
    And I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    When I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And the names of the exported files of "csv_product_grid_context_quick_export" should be "1_products_export_grid_context_fr_FR_tablet.csv,2_product_models_export_grid_context_fr_FR_tablet.csv"
    And first exported file of "csv_product_grid_context_quick_export" should contain:
      """
      sku;description-en_US-tablet;description-fr_FR-tablet
      blue-suede-shoes;Blue suede shoes;Chaussures en suedine bleues
      """

  Scenario: Successfully quick export products from grid context as a XSLX file
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
      | sku      | color | description-en_US-tablet | family   | groups | name-en_US    | price-EUR | price-USD | size |
      | boots    | black | Mob                      | boots    |        | Amazing boots | 20        | 25        | 40   |
      | sneakers | white | ylette                   | sneakers |        | Sneakers      | 50        | 60        | 42   |
      | pump     | blue  |                          |          |        | Pump          | 15        | 20        | 41   |
