Feature: Create variants of family through CSV import
  In order to setup my application
  As a product manager
  I need to be able to import new family variants

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: I successfully import a variant by color and size with two levels of variation for the family clothing
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-axes_2;variant-attributes_1;variant-attributes_2
      another_clothing_color_size;clothing;Kleidung nach Farbe und Größe;Clothing by color and size;Vêtements par couleur et taille;color;size;color,name,image,variation_image,composition;size,ean,sku,weight
      """
    When I import it via the job "csv_catalog_modeling_family_variant_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following family variants:
      | code                        | family   | label-de_DE                   | label-en_US                | label-fr_FR                     | variant-axes_1 | variant-axes_2 | variant-attributes_1                         | variant-attributes_2 |
      | another_clothing_color_size | clothing | Kleidung nach Farbe und Größe | Clothing by color and size | Vêtements par couleur et taille | color          | size           | color,name,image,variation_image,composition | size,ean,sku,weight  |

  Scenario: I successfully import a variant by size with one level of variation for the family shoes
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-attributes_1
      another_shoes_size;shoes;Schuhe nach Größe;Shoes by size;Chaussures par taille;eu_shoes_size;weight
      """
    When I import it via the job "csv_catalog_modeling_family_variant_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following family variants:
      | code               | family | label-de_DE       | label-en_US   | label-fr_FR           | variant-axes_1 | variant-attributes_1            |
      | another_shoes_size | shoes  | Schuhe nach Größe | Shoes by size | Chaussures par taille | eu_shoes_size  | ean,eu_shoes_size,sku,weight |

  Scenario: I successfully import a variant by color and size with one level of variation for the family clothing
    Given the following CSV file to import:
      """
      code;family;label-de_DE;label-en_US;label-fr_FR;variant-axes_1;variant-attributes_1
      another_clothing_color_size;clothing;Kleidung nach Farbe und Größe;Clothing by color and size;Vêtements par couleur et taille;color,size;name,image,variation_image,composition
      """
    When I import it via the job "csv_catalog_modeling_family_variant_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following family variants:
      | code                        | family   | label-de_DE                   | label-en_US                | label-fr_FR                     | variant-axes_1 | variant-attributes_1                                        |
      | another_clothing_color_size | clothing | Kleidung nach Farbe und Größe | Clothing by color and size | Vêtements par couleur et taille | color,size     | color,size,composition,ean,image,name,sku,variation_image |

  Scenario: I successfully import a variant by color and size with one level of variation for the family clothing with minimal data
    Given the following CSV file to import:
      """
      code;family;variant-axes_1
      another_clothing_color_size;clothing;color,size
      """
    When I import it via the job "csv_catalog_modeling_family_variant_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following family variants:
      | code                        | family   | variant-axes_1 |
      | another_clothing_color_size | clothing | color,size     |
