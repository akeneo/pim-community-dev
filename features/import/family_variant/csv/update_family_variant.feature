Feature: Update variants of family through CSV import
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
    And I am on the imports page

  Scenario: I successfully update family variants through CSV import
    Given the following CSV file to import:
      """
      code;family;label-en_US;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
      another_clothing_color_and_size;clothing;Clothing variant by color and size;color;size;color,name,image,variation_image,composition;size,ean,sku,weight
      another_shoes_size;shoes;Shoes variant by size;eu_shoes_size;;weight;
      another_clothing_color_size;clothing;Clothing variant by color/size;color,size;;name,image,variation_image,composition;
      """
    When I import it via the job "csv_catalog_modeling_family_variant_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "read lines 3"
    And I should see the text "processed 3"
    And there should be the following family variants:
      | code                            | family   | label-en_US                        | variant-axes_1 | variant-axes_2 | variant-attributes_1                                      | variant-attributes_2 |
      | another_clothing_color_and_size | clothing | Clothing variant by color and size | color          | size           | color,name,image,variation_image,composition              | size,ean,sku,weight  |
      | another_shoes_size              | shoes    | Shoes variant by size              | eu_shoes_size  |                | eu_shoes_size,weight,ean,sku                              |                      |
      | another_clothing_color_size     | clothing | Clothing variant by color/size     | color,size     |                | color,size,name,image,variation_image,composition,ean,sku |                      |
