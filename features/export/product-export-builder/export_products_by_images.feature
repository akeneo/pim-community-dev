@javascript
Feature: Export products according to image media attribute
  In order to export specific products
  As a product manager
  I need to be able to export the products according to image attribute values

  Background:
    Given an "apparel" catalog configuration
    And the following family:
      | code    | requirements-ecommerce | attributes                              |
      | rangers | sku, name              | attachment,description,name,price,image |
    And the following products:
      | sku        | enabled | family  | categories      | image                     | attachment             |
      | SNKRS-1C-s | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1C-s.png | %fixtures%/akeneo.txt  |
      | SNKRS-1C-t | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1C-t.png | %fixtures%/akeneo.txt  |
      | SNKRS-1R   | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1R.png   |                        |
      | SNKRS-1S   | 1       | rangers | 2014_collection |                           | %fixtures%/akeneo2.txt |
    And I am logged in as "Julia"

  Scenario: Successfully export products by their image values without using the UI
    Given the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
      | filters  | {"structure":{"locales":["en_US"],"scope":"ecommerce"},"data":[{"field": "image", "operator": "=", "value": "SNKRS-1C-s.png"}]} |
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;attachment;categories;description-en_US-ecommerce;enabled;family;groups;image;name-en_US;price-EUR;price-GBP;price-USD
    SNKRS-1C-s;files/SNKRS-1C-s/attachment/akeneo.txt;2014_collection;;1;rangers;;files/SNKRS-1C-s/image/SNKRS-1C-s.png;;;;
    """

  Scenario: Successfully export products which contains partial image value
    Given the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "ecommerce_product_export" export job edit page
    And I visit the "Content" tab
    When I add available attributes Image
    And I filter by "image" with operator "Contains" and value "KRS-1"
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;attachment;categories;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;image;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD
    SNKRS-1C-s;files/SNKRS-1C-s/attachment/akeneo.txt;2014_collection;;;;;1;rangers;;files/SNKRS-1C-s/image/SNKRS-1C-s.png;;;;;;;
    SNKRS-1C-t;files/SNKRS-1C-t/attachment/akeneo.txt;2014_collection;;;;;1;rangers;;files/SNKRS-1C-t/image/SNKRS-1C-t.png;;;;;;;
    SNKRS-1R;;2014_collection;;;;;1;rangers;;files/SNKRS-1R/image/SNKRS-1R.png;;;;;;;
    """

  Scenario: Successfully export products with empty image values
    Given the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "ecommerce_product_export" export job edit page
    And I visit the "Content" tab
    When I add available attributes Image
    And I filter by "image" with operator "Is empty" and value ""
    And I filter by "completeness" with operator "No condition on completeness" and value ""
    And I press "Save"
    Then I should not see the text "There are unsaved changes"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;attachment;categories;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;image;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD
    SNKRS-1S;files/SNKRS-1S/attachment/akeneo2.txt;2014_collection;;;;;1;rangers;;;;;;;;;
    """
