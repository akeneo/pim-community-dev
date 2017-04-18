@javascript
Feature: Execute a job
  In order to use existing product information with reference data
  As a product manager
  I need to be able to import products

  Background:
    Given the "footwear" catalog configuration
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene
    And the following "lace_fabric" attribute reference data: Spandex, Wool, Kevlar, Jute
    And the following "heel_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of products with reference data
    Given the following CSV file to import:
    """
    sku;family;heel_color;sole_fabric;lace_fabric-en_US-tablet;lace_fabric-en_US-mobile
    SKU-001;heels;Red;PVC,Nylon;;Kevlar
    SKU-002;heels;;;Jute,Spandex;Wool,Kevlar
    SKU-003;heels;Magenta;Neoprene;Wool;Jute
    SKU-004;heels;Black;Neoprene;Spandex;Spandex
    """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 4 products
    And the family of the product "SKU-001" should be "heels"
    And product "SKU-002" should be enabled
    And the product "SKU-001" should have the following values:
      | heel_color               | Red        |
      | sole_fabric              | Nylon, PVC |
      | lace_fabric-en_US-tablet |            |
      | lace_fabric-en_US-mobile | Kevlar     |
    And the product "SKU-002" should have the following values:
      | heel_color               |               |
      | sole_fabric              |               |
      | lace_fabric-en_US-tablet | Jute, Spandex |
      | lace_fabric-en_US-mobile | Kevlar, Wool  |
    And the product "SKU-003" should have the following values:
      | heel_color               | Magenta  |
      | sole_fabric              | Neoprene |
      | lace_fabric-en_US-tablet | Wool     |
      | lace_fabric-en_US-mobile | Jute     |
    And the product "SKU-004" should have the following values:
      | heel_color               | Black    |
      | sole_fabric              | Neoprene |
      | lace_fabric-en_US-tablet | Spandex  |
      | lace_fabric-en_US-mobile | Spandex  |

  Scenario: Successfully update an existing product with reference data
    Given the following product:
      | sku     | heel_color | sole_fabric | lace_fabric-en_US-tablet | lace_fabric-en_US-mobile |
      | SKU-001 | Red        | PVC,Nylon   | Kevlar,Jute,Wool         | Kevlar                   |
    And the following CSV file to import:
    """
    sku;family;heel_color;sole_fabric;lace_fabric-en_US-tablet;lace_fabric-en_US-mobile
    SKU-001;heels;Magenta;;Kevlar,Jute;Kevlar,Jute
    """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 1 product
    And the product "SKU-001" should have the following values:
      | heel_color               | Magenta      |
      | sole_fabric              |              |
      | lace_fabric-en_US-tablet | Jute, Kevlar |
      | lace_fabric-en_US-mobile | Jute, Kevlar |
