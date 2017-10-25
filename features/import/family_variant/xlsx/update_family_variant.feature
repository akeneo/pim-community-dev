@javascript
Feature: Update variants of family through XLSX import
  In order to setup my application
  As an administrator
  I need to be able to update family variants through import

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following family variants:
      | code                            | family   | label-en_US                | variant-axes_1 | variant-axes_2 | variant-attributes_1                    | variant-attributes_2 |
      | another_clothing_color_and_size | clothing | Clothing by color and size | color          | size           | color,image,variation_image,composition | size,ean,sku         |
      | another_shoes_size              | shoes    | Shoes by size              | eu_shoes_size  |                |                                         |                      |
      | another_clothing_color_size     | clothing | Clothing by color/size     | color,size     |                | name,image,variation_image              |                      |
    And I am logged in as "Peter"
    And I am on the imports page

  Scenario: I successfully update family variants through XLSX import
    Given the following XLSX file to import:
      """
      code;family;label-en_US;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
      another_clothing_color_and_size;clothing;Clothing variant by color and size;color;size;color,name,image,variation_image,composition;size,ean,sku,weight
      another_shoes_size;shoes;Shoes variant by size;eu_shoes_size;;weight;
      another_clothing_color_size;clothing;Clothing variant by color/size;color,size;;name,image,variation_image,composition;
      """
    And the following job "xlsx_catalog_modeling_family_variant_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_catalog_modeling_family_variant_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_family_variant_import" job to finish
    Then I should see the text "read lines 3"
    And I should see the text "processed 3"
    And there should be the following family variants:
      | code                            | family   | label-en_US                        | variant-axes_1 | variant-axes_2 | variant-attributes_1                                      | variant-attributes_2 |
      | another_clothing_color_and_size | clothing | Clothing variant by color and size | color          | size           | color,name,image,variation_image,composition              | size,ean,sku,weight  |
      | another_shoes_size              | shoes    | Shoes variant by size              | eu_shoes_size  |                | sku,eu_shoes_size,weight,ean                              |                      |
      | another_clothing_color_size     | clothing | Clothing variant by color/size     | color,size     |                | sku,color,size,name,image,variation_image,composition,ean |                      |
