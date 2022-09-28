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
      | uuid                                 | sku        | enabled | family  | categories      | image                     | attachment             |
      | d38f2f6b-52e3-4171-97db-23cf88b2666b | SNKRS-1C-s | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1C-s.png | %fixtures%/akeneo.txt  |
      | e6805bbe-a6f2-45ee-9908-1ae06eb05cc1 | SNKRS-1C-t | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1C-t.png | %fixtures%/akeneo.txt  |
      | 5802d434-f87c-4d41-9b20-205ad90a49e4 | SNKRS-1R   | 1       | rangers | 2014_collection | %fixtures%/SNKRS-1R.png   |                        |
      | 60e58cab-debd-4917-9a44-d6d4045526b3 | SNKRS-1S   | 1       | rangers | 2014_collection |                           | %fixtures%/akeneo2.txt |
    And I am logged in as "Julia"

  Scenario: Successfully export products according to file value start
    Given the following job "ecommerce_product_export" configuration:
      | storage   | {"type": "local", "file_path": "%tmp%/product_export/product_export.csv"} |
      | with_uuid | yes                                                                       |
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
    uuid;sku;attachment;categories;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;image;name-de_DE;name-en_GB;name-en_US;name-fr_FR;price-EUR;price-GBP;price-USD
    d38f2f6b-52e3-4171-97db-23cf88b2666b;SNKRS-1C-s;files/SNKRS-1C-s/attachment/akeneo.txt;2014_collection;;;;;1;rangers;;files/SNKRS-1C-s/image/SNKRS-1C-s.png;;;;;;;
    e6805bbe-a6f2-45ee-9908-1ae06eb05cc1;SNKRS-1C-t;files/SNKRS-1C-t/attachment/akeneo.txt;2014_collection;;;;;1;rangers;;files/SNKRS-1C-t/image/SNKRS-1C-t.png;;;;;;;
    """
