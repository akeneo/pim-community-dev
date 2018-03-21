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

  Scenario: Successfully import media
    Given the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;media_family;;bic-core-148.gif;"Bic Core 148";bic-core-148.txt;2014_collection
      fanatic-freewave-76;media_family;;fanatic-freewave-76.gif;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_product_import" contains the following media:
      | bic-core-148.gif        |
      | bic-core-148.txt        |
      | fanatic-freewave-76.gif |
      | fanatic-freewave-76.txt |
    When the products are imported via the job csv_footwear_product_import
    Then there should be 2 products
    And the product "bic-core-148" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | frontView  | fanatic-freewave-76.gif |
      | userManual | fanatic-freewave-76.txt |

  Scenario: Successfully import partial products with media attributes
    Given the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;media_family;;bic-core-148.gif;"Bic Core 148";bic-core-148.txt;2014_collection
      fanatic-freewave-76;media_family;;;"Fanatic Freewave 76";;2014_collection
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_product_import" contains the following media:
      | bic-core-148.gif |
      | bic-core-148.txt |
    When the products are imported via the job csv_footwear_product_import
    Then there should be 2 products
    And the product "bic-core-148" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | frontView  | **empty** |
      | userManual | **empty** |

  @javascript
  Scenario: Skip products with invalid media attributes
    Given I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;media_family;;bic-core-148.txt;"Bic Core 148";;2014_collection
      fanatic-freewave-76;media_family;;;"Fanatic Freewave 76";sneakers-manual.txt;2014_collection
      """
    And the following random files:
      | filename            | size |
      | sneakers-manual.txt | 3    |
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_product_import" contains the following media:
      | sneakers-manual.txt |
      | bic-core-148.txt    |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 0 products
    And I should see the text "skipped 2"
    And I should see the text "values[frontView].data: The file extension is not allowed (allowed extensions: gif, jpg)"
    And I should see the text "values[userManual].data: The file is too large (3.15 MB). Allowed maximum size is 1 MB"

  Scenario: Import several times the same media
    Given the following CSV file to import:
      """
      sku;family;groups;warranty;name-en_US;userManual;categories
      bic-core-148;media_family;;warranty.txt;"Bic Core 148";warranty.txt;2014_collection
      fanatic-freewave-76;media_family;;warranty.txt;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following random files:
      | filename            | size |
      | sneakers-manual.txt | 3    |
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_product_import" contains the following media:
      | fanatic-freewave-76.txt |
      | warranty.txt            |
    When the products are imported via the job csv_footwear_product_import
    Then there should be 2 products
    And the product "bic-core-148" should have the following values:
      | warranty   | warranty.txt |
      | userManual | warranty.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | warranty   | warranty.txt            |
      | userManual | fanatic-freewave-76.txt |

  @javascript
  Scenario: Successfully skip a product without media modification
    Given I am logged in as "Julia"
    And the following product:
      | sku                 | name-en_US          | userManual                         |
      | bic-core-148        | Bic Core 148        | %fixtures%/bic-core-148.txt        |
      | fanatic-freewave-76 | Fanatic Freewave 76 | %fixtures%/fanatic-freewave-76.txt |
      | fanatic-freewave-41 | Fanatic Freewave 41 |                                    |
      | fanatic-freewave-37 | Fanatic Freewave 37 | %fixtures%/fanatic-freewave-76.txt |
    And the following CSV file to import:
      """
      sku;name-en_US;userManual
      bic-core-148;Bic Core 148;bic-core-148.txt
      fanatic-freewave-76;Fanatic Freewave 76;bic-core-148.txt
      fanatic-freewave-41;Fanatic Freewave 41;fanatic-freewave-76.txt
      fanatic-freewave-37;Fanatic Freewave 37;
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_product_import" contains the following media:
      | bic-core-148.txt        |
      | fanatic-freewave-76.txt |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 4 products
    And I should see the text "processed 3"
    And I should see the text "skipped product (no differences) 1"
    And the product "bic-core-148" should have the following values:
      | userManual | bic-core-148.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | userManual | bic-core-148.txt |
    And the product "fanatic-freewave-41" should have the following values:
      | userManual | fanatic-freewave-76.txt |
    And the product "fanatic-freewave-37" should have the following values:
      | userManual | **empty** |
