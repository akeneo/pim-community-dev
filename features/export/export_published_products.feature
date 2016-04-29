@javascript
Feature: Export published products
  In order to export validated product data
  As a product manager
  I need to export published products

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And the missing product asset variations have been generated

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully export published products
    Given the following job "clothing_mobile_published_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/clothing_mobile_published_product_export.csv |
    And I add the "english UK" locale to the "mobile" channel
    And the following products:
      | sku          | family  | categories                 | price          | size | main_color | manufacturer |
      | jacket-white | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | white      | Volcom       |
      | jacket-black | jackets | jackets, winter_collection | 10 EUR, 15 USD | XL   | black      | Volcom       |
    And the following product values:
      | product      | attribute   | value                            | locale | scope  |
      | jacket-white | name        | White jacket                     | en_US  |        |
      | jacket-white | name        | White jacket                     | en_GB  |        |
      | jacket-white | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-white | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-black | name        | White jacket                     | en_US  |        |
      | jacket-black | name        | White jacket                     | en_GB  |        |
      | jacket-black | name        | Jacket blanc                     | fr_FR  |        |
      | jacket-black | name        | Weißes Jacket                    | de_DE  |        |
      | jacket-black | gallery     | paint                            |        |        |
      | jacket-white | description | A stylish white jacket           | en_US  | mobile |
      | jacket-white | description | An elegant white jacket          | en_GB  | mobile |
      | jacket-white | description | Un Jacket blanc élégant          | fr_FR  | mobile |
      | jacket-white | description | Ein elegantes weißes Jacket      | de_DE  | mobile |
      | jacket-white | description | A really stylish white jacket    | en_US  | mobile |
      | jacket-white | description | Ein sehr elegantes weißes Jacket | de_DE  | mobile |
      | jacket-white | gallery     | paint                            |        |        |
    And I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akeneo.jpg
    And I save the asset
    And I launched the completeness calculator
    And I edit the "jacket-white" product
    When I press the "Publish" button
    And I confirm the publishing
    And I edit the "jacket-black" product
    When I press the "Publish" button
    And I confirm the publishing
    When I am on the "clothing_mobile_published_product_export" export job page
    And I launch the export job
    And I wait for the "clothing_mobile_published_product_export" job to finish
    Then exported file of "clothing_mobile_published_product_export" should contain:
    """
    sku;categories;datasheet;description-de_DE-mobile;description-en_GB-mobile;description-en_US-mobile;description-fr_FR-mobile;enabled;family;gallery;groups;handmade;length;main_color;manufacturer;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-mobile;price-EUR;price-USD;rating;release_date-mobile;secondary_color;side_view;size;top_view;weather_conditions
    jacket-white;jackets,winter_collection;;"Ein sehr elegantes weißes Jacket";"An elegant white jacket";"A really stylish white jacket";"Un Jacket blanc élégant";1;jackets;paint;;0;;white;Volcom;"Weißes Jacket";"White jacket";"White jacket";"Jacket blanc";;10.00;15.00;;;;;XL;;
    jacket-black;jackets,winter_collection;;;;;;1;jackets;paint;;0;;black;Volcom;"Weißes Jacket";"White jacket";"White jacket";"Jacket blanc";;10.00;15.00;;;;;XL;;
    """
