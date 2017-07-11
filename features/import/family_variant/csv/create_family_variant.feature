@javascript
Feature: Create variants of family through CSV import
  In order to setup my application
  As a product manager
  I need to be able to import new family variants

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: I successfully import a variant by color and size with two levels of variation for the family clothing
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
      clothing_color_size;clothing;Kleidung nach Farbe und Größe;Clothing by color and size;Vêtements par couleur et taille;color;size;color,name,image_1,variation_image,composition;size,EAN,sku,weight
      """
    And the following job "csv_catalog_modeling_family_variant_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_family_variant_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_family_variant_import" job to finish
    Then there should be the following family variants:
      | code                | family   | label-de_DE                   | label-en_US                | label-fr_FR                     | variant-axes_1 | variant-axes_2 | variant-attributes_1                           | variant-attributes_2 |
      | clothing_color_size | clothing | Kleidung nach Farbe und Größe | Clothing by color and size | Vêtements par couleur et taille | color          | size           | color,name,image_1,variation_image,composition | size,EAN,sku,weight  |

  Scenario: I successfully import a variant by size with one level of variation for the family shoes
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-attributes_1
      shoes_size;shoes;Schuhe nach Größe;Shoes by size;Chaussures par taille;eu_shoes_size;weight
      """
    And the following job "csv_catalog_modeling_family_variant_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_family_variant_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_family_variant_import" job to finish
    Then there should be the following family variants:
      | code       | family | label-de_DE       | label-en_US    | label-fr_FR          | variant-axes_1 | variant-attributes_1 |
      | shoes_size | shoes  | Schuhe nach Größe | Shoes by size | Chaussures par taille | eu_shoes_size  | weight               |

  Scenario: I successfully import a variant by color and size with one level of variation for the family clothing
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-attributes_1
      clothing_color_size;clothing;Kleidung nach Farbe und Größe;Clothing by color and size;Vêtements par couleur et taille;color,size;name,image_1,variation_image,composition
      """
    And the following job "csv_catalog_modeling_family_variant_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_family_variant_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_family_variant_import" job to finish
    Then there should be the following family variants:
      | code                | family   | label-de_DE                   | label-en_US                | label-fr_FR                     | variant-axes_1 | variant-attributes_1                     |
      | clothing_color_size | clothing | Kleidung nach Farbe und Größe | Clothing by color and size | Vêtements par couleur et taille | color,size     | name,image_1,variation_image,composition |
