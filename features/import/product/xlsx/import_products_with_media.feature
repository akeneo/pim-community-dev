@javascript
Feature: Import media with products
  In order to re-use the images and documents I have set on my products
  As a product manager
  I need to be able to import them along with the products

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | label-en_US | type              | allowed_extensions | max_file_size | group | code       |
      | Front view  | pim_catalog_image | gif, jpg           | 1             | other | frontView  |
      | User manual | pim_catalog_file  | txt, pdf           | 1             | other | userManual |
      | Warranty    | pim_catalog_file  | txt, pdf           | 1             | other | warranty   |
    And the following family:
      | code         | attributes                         |
      | media_family | frontView,name,userManual,warranty |
    And I am logged in as "Julia"

  Scenario: Successfully import media
    Given the following XLSX file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;media_family;;bic-core-148.gif;"Bic Core 148";bic-core-148.txt;2014_collection
      fanatic-freewave-76;media_family;;fanatic-freewave-76.gif;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "xlsx_footwear_product_import" contains the following media:
      | bic-core-148.gif        |
      | bic-core-148.txt        |
      | fanatic-freewave-76.gif |
      | fanatic-freewave-76.txt |
    When I am on the "xlsx_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 2 products
    And the product "bic-core-148" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | frontView  | fanatic-freewave-76.gif |
      | userManual | fanatic-freewave-76.txt |

  Scenario: Successfully upload and import an archive
    Given I am on the "xlsx_footwear_product_import" import job page
    When I upload and import the file "caterpillar_import_xlsx.zip"
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then there should be 3 products
    And product "CAT-001" should be enabled
    And product "CAT-002" should be enabled
    And product "CAT-003" should be enabled
    And the family of "CAT-001" should be "boots"
    And the family of "CAT-002" should be "boots"
    And the family of "CAT-003" should be "boots"
    And the category of the product "CAT-001" should be "winter_collection"
    And the category of the product "CAT-002" should be "winter_collection"
    And the category of the product "CAT-003" should be "winter_collection"
    And the english localizable value name of "CAT-001" should be "Caterpillar 1"
    And the english localizable value name of "CAT-002" should be "Caterpillar 2"
    And the english localizable value name of "CAT-003" should be "Caterpillar 3"
    And the english mobile description of "CAT-001" should be "Model 1 boots"
    And the english mobile description of "CAT-002" should be "Model 2 boots"
    And the english mobile description of "CAT-003" should be "Model 3 boots"
    And the product "CAT-001" should have the following values:
      | side_view | cat_001.png |
    And the product "CAT-002" should have the following values:
      | side_view | cat_002.png |
    And the product "CAT-003" should have the following values:
      | side_view | cat_003.png |
