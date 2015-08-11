@deprecated @javascript
Feature: Import media with products
  In order to re-use the images and documents I have set on my products
  As a product manager
  I need to be able to import them along with the products

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | label       | type  | allowed extensions | max file size |
      | Front view  | image | gif, jpg           | 1             |
      | User manual | file  | txt, pdf           | 1             |
      | Warranty    | file  | txt, pdf           | 1             |
    And I am logged in as "Julia"

  Scenario: Successfully import media
    Given the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;sneakers;;bic-core-148.gif;"Bic Core 148";bic-core-148.txt;2014_collection
      fanatic-freewave-76;sneakers;;fanatic-freewave-76.gif;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_product_import" contains the following media:
      | bic-core-148.gif        |
      | bic-core-148.txt        |
      | fanatic-freewave-76.gif |
      | fanatic-freewave-76.txt |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
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
    bic-core-148;sneakers;;bic-core-148.gif;"Bic Core 148";bic-core-148.txt;2014_collection
    fanatic-freewave-76;sneakers;;;"Fanatic Freewave 76";;2014_collection
    """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_product_import" contains the following media:
      | bic-core-148.gif |
      | bic-core-148.txt |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 2 products
    And the product "bic-core-148" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | frontView  | **empty** |
      | userManual | **empty** |

  Scenario: Skip products with invalid media attributes
    Given the following CSV file to import:
      """
      sku;family;groups;frontView;name-en_US;userManual;categories
      bic-core-148;sneakers;;bic-core-148.txt;"Bic Core 148";;2014_collection
      fanatic-freewave-76;sneakers;;;"Fanatic Freewave 76";sneakers-manual.txt;2014_collection
      """
    And the following random files:
      | filename            | size |
      | sneakers-manual.txt | 3    |
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_product_import" contains the following media:
      | sneakers-manual.txt |
      | bic-core-148.txt    |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 0 products
    And I should see "skipped 2"
    And I should see "frontView: The file extension is not allowed (allowed extensions: gif, jpg)"
    And I should see "userManual: The file is too large (3.15 MB). Allowed maximum size is 1 MB"

  Scenario: Import several times the same media
    Given the following CSV file to import:
      """
      sku;family;groups;warranty;name-en_US;userManual;categories
      bic-core-148;sneakers;;warranty.txt;"Bic Core 148";warranty.txt;2014_collection
      fanatic-freewave-76;sneakers;;warranty.txt;"Fanatic Freewave 76";fanatic-freewave-76.txt;2014_collection
      """
    And the following random files:
      | filename            | size |
      | sneakers-manual.txt | 3    |
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_product_import" contains the following media:
      | fanatic-freewave-76.txt |
      | warranty.txt            |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 2 products
    And the product "bic-core-148" should have the following values:
      | warranty   | warranty.txt |
      | userManual | warranty.txt |
    And the product "fanatic-freewave-76" should have the following values:
      | warranty   | warranty.txt            |
      | userManual | fanatic-freewave-76.txt |
