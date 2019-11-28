@javascript
Feature: Create valid variants of family through CSV import
  In order to setup my application
  As a product manager
  I need to be able to skip invalid family variants

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully skipped family variants without family during import
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
      another_clothing_color_size;clothing;Kleidung nach Farbe und Größe;Clothing by color and size;Vêtements par couleur et taille;color;size;color,name,image,variation_image,composition;size,ean,sku,weight
      another_shoes_size;shoes;Schuhe nach Größe;Shoes by size;Chaussures par taille;eu_shoes_size;;weight;
      another_clothing_color_size;;Kleidung nach Farbe/Größe;Clothing by color/size;Vêtements par couleur/taille;color,size;;name,image,variation_image,composition;
      """
    And the following job "csv_catalog_modeling_family_variant_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_family_variant_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_family_variant_import" job to finish
    Then there should be 10 family variants
    And I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Field \"family\" must be filled"
    And the invalid data file of "csv_catalog_modeling_family_variant_import" should contain:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
      another_clothing_color_size;;Kleidung nach Farbe/Größe;Clothing by color/size;Vêtements par couleur/taille;color,size;;name,image,variation_image,composition;
      """
