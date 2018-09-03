Feature: Import variant products that were previously products
  In order to import my variant products
  As a catalog manager
  I need to be able to convert a product to a variant product

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following root product models:
      | code      | family_variant      | description-en_US-ecommerce      |
      | model-col | clothing_color_size | Magnificent Cult of Luna t-shirt |
      | model-nin | clothing_size       |                                  |
    And the following sub product models:
      | code            | parent    | family_variant      | color | composition             |
      | model-col-white | model-col | clothing_color_size | white | cotton 90%, viscose 10% |
    And the following products:
      | sku          | family   | color | size | description-en_US-ecommerce | composition | weight   | categories |
      | col-white-m  | clothing | white | m    | Cult of Luna tee            | 100% cotton | 478 GRAM | tshirts    |
      | col-white-xl | clothing | white | xl   | Cult of Luna tee            | 100% cotton | 478 GRAM | tshirts    |
      | nin-s        | clothing |       | s    | Nine Inch Nails tee         | 100% cotton |          | tshirts    |

  Scenario: Converting a product to a variant product inside a family variant with 2 levels of hierarchy
    Given the following CSV file to import:
      """
      sku;parent
      col-white-m;model-col-white
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the parent of the product "col-white-m" should be "model-col-white"

  Scenario: Converting a product to a variant product inside a family variant with 1 levels of hierarchy
    Given the following CSV file to import:
      """
      sku;parent
      nin-s;model-nin
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the parent of the product "nin-s" should be "model-nin"

  Scenario: Converting a product to a variant product overwrites the values already defined in its ancestry
    Given the following CSV file to import:
      """
      sku;parent
      col-white-m;model-col-white
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the product "col-white-m" should have the following values:
      | description-en_US-ecommerce | Magnificent Cult of Luna t-shirt |
      | composition                 | cotton 90%, viscose 10%          |

  Scenario: Converting a product to a variant product overwrites the empty or non defined values in its ancestry
    Given the following CSV file to import:
      """
      sku;parent
      nin-s;model-nin
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the product "nin-s" should have the following values:
      | description-en_US-ecommerce |  |
      | composition                 |  |

  Scenario: Update the values defined in the last level of the variant of the family
    Given the following CSV file to import:
      """
      sku;parent;weight
      col-white-m;model-col-white;150 GRAM
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the product "col-white-m" should have the following values:
      | weight | 150.0000 GRAM |

  Scenario: Create a product, variant product and convert a product to a variant product in the same import
    Given the following CSV file to import:
      """
      sku;parent;weight;family;size
      normal-product;;150 GRAM;clothing;
      col-white-l;model-col-white;150 GRAM;clothing;l
      col-white-m;model-col-white;150 GRAM;clothing;m
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then "normal-product" should be a product
    And "col-white-l" should be a variant product
    And "col-white-m" should be a variant product

  Scenario: Do not convert a product to variant product if the data from the CSV file are invalid
    Given the following CSV file to import:
      """
      sku;parent;weight;family;size
      col-white-s;model-col-white;150 GRAM;clothing;s
      col-white-xl;model-col-white;150 GRAM;wrong_family;s
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then "col-white-xl" should be a product
