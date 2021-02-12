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
    And the following "color" attribute options: red, black, white
    And the following family:
      | code | attribute_requirements | attributes                  |
      | bags | ecommerce-sku          | color,description,name,size |
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

  @integration-back @purge-messenger
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
    When I launch the "csv_product_model_import_with_rules" import job
    Then the en_US unscoped name of "bag_model" should be "I have no name"
    And the en_US unscoped name of "another_model" should be "The other bag"
    And the en_US unscoped name of "new_model" should be "I have no name"
    And the en_US ecommerce description of "black_bag" should be "A useless description"
    And the en_US ecommerce description of "red_bag" should be "The original description"
    And the en_US ecommerce description of "white_bag" should be "A useless description"
    And 6 events of type "product_model.updated" should have been raised

  @integration-back
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
    When I launch the "xlsx_product_model_import_with_rules" import job
    Then the en_US unscoped name of "bag_model" should be "I have no name"
    And the en_US unscoped name of "another_model" should be "The other bag"
    And the en_US unscoped name of "new_model" should be "I have no name"
    And the en_US ecommerce description of "black_bag" should be "A useless description"
    And the en_US ecommerce description of "red_bag" should be "The original description"
    And the en_US ecommerce description of "white_bag" should be "A useless description"
