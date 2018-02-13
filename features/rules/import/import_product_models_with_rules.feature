@javascript
Feature: Import product models with rules
  In order ease the enrichment of the catalog
  As a regular user
  I need to be able to import product models and apply rules

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                     | localizable | scopable | group | decimals_allowed |
      | color       | Color       | pim_catalog_simpleselect | 0           | 0        | other |                  |
      | description | Description | pim_catalog_textarea     | 1           | 1        | other |                  |
      | name        | Name        | pim_catalog_text         | 1           | 0        | other |                  |
      | size        | Size        | pim_catalog_simpleselect | 0           | 0        | other |                  |
    And the following "color" attribute options: red, black and white
    And the following family:
      | code | requirements-ecommerce | requirements-ecommerce | attributes                  |
      | bags | sku                    | sku                    | color,description,name,size |
    And the following family variants:
      | code           | family | variant-axes_1 | variant-attributes_1 | variant-axes_2 | variant-attributes_2 |
      | bag_color_size | bags   | color          | color,description    | size           | size                 |
    And the following root product model:
      | code          | categories | family_variant | name-en_US    |
      | bag_model     | default    | bag_color_size | The bag!      |
      | another_model | default    | bag_color_size | The other bag |
    And the following sub product models:
      | code      | categories | parent    | family_variant | color | description-en_US-ecommerce |
      | black_bag | default    | bag_model | bag_color_size | black |                             |
      | red_bag   | default    | bag_model | bag_color_size | red   | The original description    |
    And I am logged in as "Peter"
    And the following product rule definitions:
      """
      set_bag_name:
        priority: 10
        conditions:
          - field: categories
            operator: IN
            value:
              - default
        actions:
          - type: set
            field: name
            value: I have no name
            locale: en_US
      set_bag_description:
        priority: 10
        conditions:
          - field: categories
            operator: IN
            value:
              - default
        actions:
          - type: set
            field: description
            value: A useless description
            locale: en_US
            scope: ecommerce
      """

  Scenario: Apply rules on product models imported in CSV
    Given the following CSV file to import:
      """
      code;parent;family_variant;categories;color;description-en_US-ecommerce;name-en_US
      bag_model;;bag_color_size;default;;;A name
      new_model;;bag_color_size;default;;;Another name
      black_bag;bag_model;bag_color_size;default;black;A description;
      white_bag;bag_model;bag_color_size;default;white;A description;
      """
    And the following job:
      | connector            | type   | alias                               | code                                | label                               |
      | Akeneo CSV Connector | import | csv_product_model_import_with_rules | csv_product_model_import_with_rules | CSV product model import with rules |
    And the following job "csv_product_model_import_with_rules" configuration:
      | filePath | %file to import% |
    When I am on the "csv_product_model_import_with_rules" import job page
    And I launch the import job
    And I wait for the "csv_product_model_import_with_rules" job to finish
    Then there should be the following root product models:
      | code          | name-en_US     |
      | bag_model     | I have no name |
      | another_model | The other bag  |
      | new_model     | I have no name |
    Then there should be the following product models:
      | code      | description-en_US-ecommerce |
      | black_bag | A useless description       |
      | red_bag   | The original description    |
      | white_bag | A useless description       |

  Scenario: Apply rules on product models imported in XLSX
    Given the following XLSX file to import:
      """
      code;parent;family_variant;categories;color;description-en_US-ecommerce;name-en_US
      bag_model;;bag_color_size;default;;;A name
      new_model;;bag_color_size;default;;;Another name
      black_bag;bag_model;bag_color_size;default;black;A description;
      white_bag;bag_model;bag_color_size;default;white;A description;
      """
    And the following job:
      | connector             | type   | alias                                | code                                 | label                                |
      | Akeneo XLSX Connector | import | xlsx_product_model_import_with_rules | xlsx_product_model_import_with_rules | XLSX product model import with rules |
    And the following job "xlsx_product_model_import_with_rules" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_product_model_import_with_rules" import job page
    And I launch the import job
    And I wait for the "xlsx_product_model_import_with_rules" job to finish
    Then there should be the following root product models:
      | code          | name-en_US     |
      | bag_model     | I have no name |
      | another_model | The other bag  |
      | new_model     | I have no name |
    Then there should be the following product models:
      | code      | description-en_US-ecommerce |
      | black_bag | A useless description       |
      | red_bag   | The original description    |
      | white_bag | A useless description       |
