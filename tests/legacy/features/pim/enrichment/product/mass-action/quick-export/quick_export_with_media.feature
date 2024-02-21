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
      | uuid                                 | sku      | family   | categories        | name-en_US    | price          | size | color | 123 | side_view              |
      | d48adc7a-e3e5-470e-9393-32812cd23e5c | boots    | boots    | winter_collection | Amazing boots | 20 EUR, 25 USD | 40   | black | aaa | %fixtures%/akeneo.jpg  |
      | 9f7ef881-54a2-419f-804b-4bef1b5c0023 |          | sneakers | summer_collection | Sneakers      | 50 EUR, 60 USD | 42   | white | bbb | %fixtures%/akeneo2.jpg |
      | b0d2f421-50d6-42cc-9e9c-7298b9580a16 | sandals  | sandals  | summer_collection | Sandals       | 5 EUR, 5 USD   | 40   | red   | ccc |                        |
      | ce54cde5-8b1e-444b-8162-4cc834330672 | pump     |          | summer_collection | Pump          | 15 EUR, 20 USD | 41   | blue  | ddd |                        |

  Scenario: Successfully quick export products and media as a XLSX file
    Given I am on the products grid
    When I select rows boots, Sneakers
    And I press the "Quick Export" button
    And I press the "XLSX" button
    And I press the "All attributes" button
    And I press the "With codes" button
    And I press the "With media" button
    And I press the "With UUID" button
    And I press the "Export" button
    And I wait for the "xlsx_product_quick_export" quick export to finish
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                      |
      | success | Quick Export Quick export XLSX product quick export finished |
    When I go on the last executed job resume of "xlsx_product_quick_export"
    Then I should see the text "COMPLETED"
    And the names of the exported files of "xlsx_product_quick_export" should be "1_products_export_en_US_tablet.xlsx"
    And exported xlsx file of "xlsx_product_quick_export" should contain:
      | uuid                                 | sku   | 123 | categories        | color | description-en_US-tablet | enabled | family   | groups | lace_color | manufacturer | name-en_US    | price-EUR | price-USD | rating | side_view                                                        | size | top_view | weather_conditions |
      | d48adc7a-e3e5-470e-9393-32812cd23e5c | boots | aaa | winter_collection | black |                          | 1       | boots    |        |            |              | Amazing boots | 20        | 25        |        | files/boots/side_view/akeneo.jpg                                 | 40   |          |                    |
      | 9f7ef881-54a2-419f-804b-4bef1b5c0023 |       | bbb | summer_collection | white |                          | 1       | sneakers |        |            |              | Sneakers      | 50        | 60        |        | files/9f7ef881-54a2-419f-804b-4bef1b5c0023/side_view/akeneo2.jpg | 42   |          |                    |

  Scenario: Successfully quick export products with media without selecting the attribute media in the grid
    Given I am on the products grid
    When I select rows boots, sandals
    And I press the "Quick Export" button
    And I press the "CSV" button
    And I press the "Grid context" button
    And I press the "With codes" button
    And I press the "With media" button
    And I press the "Without UUID" button
    And I press the "Export" button
    And I wait for the "csv_product_grid_context_quick_export" quick export to finish
    And I go on the last executed job resume of "csv_product_grid_context_quick_export"
    Then I should see the text "COMPLETED"
    And first exported file of "csv_product_grid_context_quick_export" should contain:
      """
      sku;enabled;family
      boots;1;boots
      sandals;1;sandals
      """
