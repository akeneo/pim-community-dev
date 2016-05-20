@javascript
Feature: Export published products in XLSX
  In order to export validated product data
  As a product manager
  I need to export published products in XLSX

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export published products into several files
    Given the following job "xlsx_clothing_mobile_published_product_export" configuration:
      | filePath     | %tmp%/ecommerce_product_export/xlsx_clothing_mobile_published_product_export.xlsx |
      | linesPerFile | 2                                                                                 |
    And the following products:
      | sku          | family  | categories                 | price          | size | main_color | manufacturer |
      | jacket-white | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | white      | Volcom       |
      | jacket-black | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | black      | Volcom       |
      | jacket-blue  | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | blue       | Volcom       |
    And the following product values:
      | product      | attribute   | value                            | locale | scope  |
      | jacket-white | name        | White jacket                     | en_US  |        |
      | jacket-white | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-white | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-black | name        | White jacket                     | en_US  |        |
      | jacket-black | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-black | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-blue  | name        | Blue jacket                      | en_US  |        |
      | jacket-blue  | name        | Jacket bleu                      | fr_FR  |        |
      | jacket-blue  | name        | Blau Jacket                      | de_DE  |        |
      | jacket-black | gallery     | paint                            |        |        |
      | jacket-blue  | gallery     | paint                            |        |        |
      | jacket-white | description | A stylish white jacket           | en_US  | mobile |
      | jacket-white | description | Un Jacket blanc élégant          | fr_FR  | mobile |
      | jacket-white | description | Ein elegantes weißes Jacket      | de_DE  | mobile |
      | jacket-white | description | A really stylish white jacket    | en_US  | mobile |
      | jacket-white | description | Ein sehr elegantes weißes Jacket | de_DE  | mobile |
      | jacket-white | gallery     | paint                            |        |        |
    When I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akeneo.jpg
    Then I save the asset
    And I launched the completeness calculator
    When I edit the "jacket-white" product
    Then I press the "Publish" button
    And I confirm the publishing
    When I edit the "jacket-black" product
    Then I press the "Publish" button
    And I confirm the publishing
    When I edit the "jacket-blue" product
    Then I press the "Publish" button
    And I confirm the publishing
    When I am on the "xlsx_clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "xlsx_clothing_mobile_published_product_export" job to finish
    Then exported xlsx file 1 of "xlsx_clothing_mobile_published_product_export" should contain:
      | sku          | categories                | datasheet | description-de_DE-mobile         | description-en_US-mobile      | description-fr_FR-mobile | enabled | family  | gallery | groups | handmade | length | length-unit | main_color | manufacturer | name-de_DE    | name-en_US   | name-fr_FR   | number_in_stock-mobile | price-EUR | price-USD | rating | release_date-mobile | secondary_color | side_view | size | top_view | weather_conditions |
      | jacket-white | jackets,winter_collection |           | Ein sehr elegantes weißes Jacket | A really stylish white jacket | Un Jacket blanc élégant  | 1       | jackets | paint   |        | 0        |        |             | white      | Volcom       | Weißes Jacket | White jacket | Jacket blanc |                        | 10.00     | 15.00     |        |                     |                 |           | XL   |          |                    |
      | jacket-black | jackets,winter_collection |           |                                  |                               |                          | 1       | jackets | paint   |        | 0        |        |             | black      | Volcom       | Weißes Jacket | White jacket | Jacket blanc |                        | 10.00     | 15.00     |        |                     |                 |           | XL   |          |                    |
    And exported xlsx file 2 of "xlsx_clothing_mobile_published_product_export" should contain:
      | sku          | categories                | datasheet | description-de_DE-mobile | description-en_US-mobile | description-fr_FR-mobile | enabled | family  | gallery | groups | handmade | length | length-unit | main_color | manufacturer | name-de_DE  | name-en_US  | name-fr_FR  | number_in_stock-mobile | price-EUR | price-USD | rating | release_date-mobile | secondary_color | side_view | size | top_view | weather_conditions |
      | jacket-blue  | jackets,winter_collection |           |                          |                          |                          | 1       | jackets | paint   |        | 0        |        |             |blue       | Volcom       | Blau Jacket | Blue jacket | Jacket bleu |                        | 10.00     | 15.00     |        |                     |                 |           | XL   |          |                    |
