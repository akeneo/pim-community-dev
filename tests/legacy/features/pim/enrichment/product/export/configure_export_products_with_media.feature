@javascript
Feature: Configure export of products media
  In order to control the data I get from a product export
  As a product manager
  I need to be able to configure a product export regarding media export policy

  Background:
    Given a "footwear" catalog configuration
    And the following jobs:
      | connector             | type   | alias               | code                | label                        |
      | Akeneo XLSX Connector | export | xlsx_product_export | xlsx_product_export | XLSX footwear product export |
    And the following products:
      | uuid                                 | sku           | name-en_US    | price-EUR | size | color | side_view             | family | categories        |
      | bd9859a1-bbd9-4714-a5d8-74858e7e41cd | gothic_boot_1 | Gothic Boot A | 19.99     | 35   | black | %fixtures%/akeneo.jpg | boots  | winter_collection |
      | 42541333-ec27-4d00-8267-3f9a33ed81c1 | gothic_boot_2 | Gothic Boot B | 24.99     | 35   | black | %fixtures%/akeneo.jpg | boots  | winter_collection |
      | d31edfac-b656-4d24-897f-9445e0566470 | gothic_boot_3 | Gothic Boot C | 29.99     | 35   | black | %fixtures%/akeneo.jpg | boots  | winter_collection |
      | 981b5307-501d-47cd-9228-eb1b51ab1cff | gothic_boot_4 | Gothic Boot D | 49.99     | 35   | black | %fixtures%/akeneo.jpg | boots  | winter_collection |

  Scenario: Successfully export products in csv with media
    Given the following job "csv_footwear_product_export" configuration:
      | storage    | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"} |
      | with_media | yes                                                                       |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then I should see "Download generated archive" on the "Download generated files" dropdown button
    And export directory of "csv_footwear_product_export" should contain the following file:
      | product_export.csv |
    And export directory of "csv_footwear_product_export" should contain the following media:
      | files/gothic_boot_1/side_view/akeneo.jpg |
      | files/gothic_boot_2/side_view/akeneo.jpg |
      | files/gothic_boot_3/side_view/akeneo.jpg |
      | files/gothic_boot_4/side_view/akeneo.jpg |

  Scenario: Successfully export products in xlsx with media
    Given the following job "xlsx_product_export" configuration:
      | storage | {"type": "local", "file_path": "%tmp%/product_export/product_export.xlsx"} |
      | with_media | yes                                                              |
      | filters    | {"structure":{"locales":["en_US"],"scope":"mobile"}, "data": []} |
    And I am logged in as "Julia"
    And I am on the "xlsx_product_export" export job page
    And I launch the export job
    And I wait for the "xlsx_product_export" job to finish
    Then I should see "Download generated archive" on the "Download generated files" dropdown button
    And export directory of "xlsx_product_export" should contain the following file:
      | product_export.xlsx |
    And export directory of "xlsx_product_export" should contain the following media:
      | files/gothic_boot_1/side_view/akeneo.jpg |
      | files/gothic_boot_2/side_view/akeneo.jpg |
      | files/gothic_boot_3/side_view/akeneo.jpg |
      | files/gothic_boot_4/side_view/akeneo.jpg |
