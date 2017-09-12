@javascript
Feature: Export products according to file media attribute
  In order to export specific products
  As a product manager
  I need to be able to export the products according to file attribute values

  Background:
    Given an "apparel" catalog configuration
    And the following family:
      | code    | requirements-ecommerce | attributes                              |
      | rangers | sku,name               | attachment,description,image,name,price |
    And the following products:
      | sku        | enabled | family  | categories      | image                     | attachment             |
      | SNKRS-1C-s | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1C-s.png | %fixtures%/akeneo.txt  |
      | SNKRS-1C-t | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1C-t.png | %fixtures%/akeneo.txt  |
      | SNKRS-1R   | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1R.png   |                        |
      | SNKRS-1S   | 1       | rangers | 2014_collection |                           | %fixtures%/akeneo2.txt |
    And I am logged in as "Julia"

  Scenario: Successfully export products according to file value start
    Given the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And I am on the "ecommerce_product_export" export job edit page
    And I visit the "Content" tab
    When I add available attributes Attachment
    And I filter by "attachment" with operator "Starts with" and value "akeneo.t"
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
    """
