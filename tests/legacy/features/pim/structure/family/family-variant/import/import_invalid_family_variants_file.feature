@javascript
Feature: Validate imported files of family variant
  In order to setup my application
  As a product manager
  I need to be able to import valid files of family variant

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully stop import if code column is missing
    Given the following CSV file to import:
      """
      family;label-en_US;variant-axes_1;variant-attributes_1
      clothing;Clothing by color and size;color,size;color,name,image,variation_image,composition,size,ean,sku,weight
      """
    And the following job "csv_catalog_modeling_family_variant_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_family_variant_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_family_variant_import" job to finish
    Then there should be 8 family variants
    And I should see the text "Status: Failed"
    And I should see the text "Field \"code\" is expected, provided fields are \"family, label-en_US, variant-axes_1, variant-attributes_1\""
