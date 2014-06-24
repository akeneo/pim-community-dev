@javascript
Feature: Import media with products
  In order to re-use the images and documents I have set on my products
  As Julia
  I need to be able to import them along with the products

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | label       | type  | allowed extensions |
      | Front view  | image | gif, jpg           |
      | User manual | file  | txt, pdf           |
    And I am logged in as "Julia"

  Scenario: Successfully import media
    Given the following file to import:
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
    Given the following file to import:
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

  Scenario: Fail to import product with media attributes if the media doesn't actually exist
    Given the following file to import:
    """
    sku;family;groups;frontView;name-en_US;userManual;categories
    fanatic-freewave-76;sneakers;;fanatic-freewave-76.jpg;"Fanatic Freewave 76";fanatic-freewave-76.pdf;2014_collection
    """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 0 products
    And I should see "frontView: File not found"
    And I should see "userManual: File not found"
