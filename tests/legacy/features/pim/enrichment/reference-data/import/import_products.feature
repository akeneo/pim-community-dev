@javascript
Feature: Execute a job
  In order to use existing product information with reference data
  As a product manager
  I need to be able to import products

  Background:
    Given the "footwear" catalog configuration
    And the following family:
      | code      | attributes                         |
      | new_heels | sole_fabric,lace_fabric,heel_color |
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully import a csv file of products with reference data
    Given the following CSV file to import:
      """
      sku;family;heel_color;sole_fabric;lace_fabric-en_US-tablet;lace_fabric-en_US-mobile
      SKU-001;new_heels;Red;viyella,Nylon;;Kevlar
      SKU-002;new_heels;;;Jute,Spandex;Wool,Kevlar
      SKU-003;new_heels;Magenta;Neoprene;Wool;Jute
      SKU-004;new_heels;Black;Neoprene;Spandex;Spandex
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then there should be 4 products
    And the family of the product "SKU-001" should be "new_heels"
    And product "SKU-002" should be enabled
    And the product "SKU-001" should have the following values:
      | heel_color               | [red]              |
      | sole_fabric              | [nylon], [viyella] |
      | lace_fabric-en_US-tablet |                    |
      | lace_fabric-en_US-mobile | [kevlar]           |
    And the product "SKU-002" should have the following values:
      | heel_color               |                   |
      | sole_fabric              |                   |
      | lace_fabric-en_US-tablet | [jute], [spandex] |
      | lace_fabric-en_US-mobile | [kevlar], [wool]  |
    And the product "SKU-003" should have the following values:
      | heel_color               | [magenta]  |
      | sole_fabric              | [neoprene] |
      | lace_fabric-en_US-tablet | [wool]     |
      | lace_fabric-en_US-mobile | [jute]     |
    And the product "SKU-004" should have the following values:
      | heel_color               | [black]    |
      | sole_fabric              | [neoprene] |
      | lace_fabric-en_US-tablet | [spandex]  |
      | lace_fabric-en_US-mobile | [spandex]  |
