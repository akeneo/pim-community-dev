@javascript
Feature: Quick export many products with media from datagrid
  In order to quick export a set of products
  As a product manager
  I need to be able to display products I want and export them

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the "boots" family page
    And I visit the "Attributes" tab
    And I add available attribute Attribute 123
    And I save the family
    And I am on the "sneakers" family page
    And I visit the "Attributes" tab
    And I add available attribute Attribute 123
    And I save the family
    And I am on the "sandals" family page
    And I visit the "Attributes" tab
    And I add available attribute Attribute 123
    And I save the family
    And the following products:
      | sku      | family   | categories        | name-en_US    | price          | size | color | 123 | side_view              |
      | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black | aaa | %fixtures%/akeneo.jpg  |
      | sneakers | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white | bbb | %fixtures%/akeneo2.jpg |
      | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   | ccc |                        |
      | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | ddd |                        |

  Scenario: Successfully quick export products and media as a XLSX file
    Given I am on the products grid
    When I select rows boots, sneakers
    And I press "Excel (All attributes)" on the "Quick Export" dropdown button
    And I wait for the "xlsx_product_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                      |
      | success | Quick Export Quick export XLSX product quick export finished |
    When I go on the last executed job resume of "xlsx_product_quick_export"
    Then I should see the text "COMPLETED"
    And the names of the exported files of "xlsx_product_quick_export" should be "1_products_export_en_US_tablet.xlsx,2_product_models_export_en_US_tablet.xlsx"
    And exported xlsx file 1 of "xlsx_product_quick_export" should contain:
      | sku      | 123 | categories        | color | description-en_US-tablet | enabled | family   | groups | lace_color | manufacturer | name-en_US    | price-EUR | price-USD | rating | side_view                            | size | top_view | weather_conditions |
      | boots    | aaa | winter_collection | black |                          | 1       | boots    |        |            |              | Amazing boots | 20        | 25        |        | files/boots/side_view/akeneo.jpg     | 40   |          |                    |
      | sneakers | bbb | summer_collection | white |                          | 1       | sneakers |        |            |              | Sneakers      | 50        | 60        |        | files/sneakers/side_view/akeneo2.jpg | 42   |          |                    |
    And export directory of "xlsx_product_quick_export" should contain the following media:
      | files/boots/side_view/akeneo.jpg     |
      | files/sneakers/side_view/akeneo2.jpg |

  Scenario: Successfully quick export products with media without selecting the attribute media in the grid
    Given I am on the products grid
    When I select rows boots, sandals
    And I press "CSV (Grid context)" on the "Quick Export" dropdown button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And first exported file of "csv_product_grid_context_quick_export" should contain:
      """
      sku;enabled;family
      boots;1;boots
      sandals;1;sandals
      """
    And export directory of "csv_product_quick_export" should not contain the following media:
      | files/boots/side_view/akeneo.jpg     |
      | files/sneakers/side_view/akeneo2.jpg |
