@javascript
Feature: Export family variants in XLSX
  In order to be able to access and modify family variants outside PIM
  As a product manager
  I need to be able to export family variants in XLSX

  Scenario: Successfully export catalog family variants
    Given a "catalog_modeling" catalog configuration
    And the following job "xlsx_catalog_modeling_family_variant_export" configuration:
      | filePath | %tmp%/family_variant.xlsx |
    And I am logged in as "Julia"
    And I am on the "xlsx_catalog_modeling_family_variant_export" export job page
    When I launch the export job
    And I wait for the "xlsx_catalog_modeling_family_variant_export" job to finish
    Then I should see the text "Read 8"
    And I should see the text "Written 8"
    And exported xlsx file of "xlsx_catalog_modeling_family_variant_export" should contain:
      | code                   | family      | label-de_DE                   | label-en_US                   | label-fr_FR                      | variant-axes_1 | variant-axes_2 | variant-attributes_1                                          | variant-attributes_2                            |
      | clothing_color_size    | clothing    | Kleidung nach Farbe und Größe | Clothing by color and size    | Vêtements par couleur et taille  | color          | size           | variation_name,variation_image,composition,color,material     | sku,weight,size,ean                             |
      | shoes_size             | shoes       | Schuhe nach Größe             | Shoes by size                 | Chaussures par taille            | eu_shoes_size  |                | sku,weight,size,eu_shoes_size,ean                             |                                                 |
      | clothing_colorsize     | clothing    | Kleidung nach Farbe/Größe     | Clothing by color/size        | Vêtements par couleur/taille     | color,size     |                | sku,variation_name,variation_image,composition,color,size,ean |                                                 |
      | clothing_size          | clothing    | Kleidung nach Größe           | Clothing by size              | Vêtements par taille             | size           |                | sku,weight,size,ean                                           |                                                 |
      | clothing_color         | clothing    | Kleidung nach Farbe           | Clothing by color             | Vêtements par couleur            | color          |                | sku,variation_name,variation_image,composition,color,ean      |                                                 |
      | accessories_size       | accessories | Accessories by size           | Accessories by size           | Accessoires par taille           | size           |                | sku,weight,variation_name,size,ean                            |                                                 |
      | shoes_size_color       | shoes       | Schuhe nach Größe und Farbe   | Shoes by size and color       | Chaussures par taille et couleur | size           | color          | weight,variation_name,size,eu_shoes_size                      | sku,image,variation_image,composition,color,ean |
      | clothing_material_size | clothing    | Clothing by material and size | Clothing by material and size | Vêtements par matière et taille  | material       | size           | variation_name,variation_image,composition,color,material     | sku,weight,size,ean                             |
