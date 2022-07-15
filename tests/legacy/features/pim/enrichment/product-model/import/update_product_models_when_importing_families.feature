Feature: Update product models when importing families
  In order to enrich correctly my catalog
  As a product manager
  I need to update the product models when families are imported

  # should be more precise:
  # - attribute as required for the completeness (or not)
  # - adding an attribute to the family
  @critical @javascript
  Scenario: Successfully update an existing family computes all product models data in a dedicated step for csv
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And the product model value material of "model-braided-hat" should be "wool"
      # Removed the 'material' attributes from the 'accessories' + remove 'collection' attribute requirement for ecommerce
    And the following CSV file to import:
      """
      code;label-de_DE;label-en_US;label-fr_FR;attributes;attribute_as_image;attribute_as_label;requirements-ecommerce;requirements-mobile;requirements-print
      accessories;Accessories;Accessories;Accessories;brand,collection,color,composition,ean,erp_name,image,keywords,meta_description,meta_title,name,notice,price,size,sku,supplier,variation_image,variation_name,weight;image;name;name,sku,variation_name,weight;collection,name,sku,variation_name,weight;collection,name,sku,variation_name,weight
      """
    And the following job "csv_catalog_modeling_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_family_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_family_import" job to finish
    And I should see the text "Family import"
    And I should see the text "Compute root product models data"
    And I should see the text "Compute sub product models data"
    And I should see the text "Compute products data"
    And the product model "model-braided-hat" should not have the following values "material"
    And there should only be the following job instance executed:
      | job_instance                       | times |
      | csv_catalog_modeling_family_import | 1     |

  @javascript
  Scenario: Successfully update an existing family computes of product models in a dedicated step for xlsx
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And the product model value material of "model-braided-hat" should be "wool"
      # Removed the 'material' attributes from the 'accessories' + remove 'collection' attribute requirement for ecommerce
    And the following XLSX file to import:
      """
      code;label-de_DE;label-en_US;label-fr_FR;attributes;attribute_as_image;attribute_as_label;requirements-ecommerce;requirements-mobile;requirements-print
      accessories;Accessories;Accessories;Accessories;brand,collection,color,composition,ean,erp_name,image,keywords,meta_description,meta_title,name,notice,price,size,sku,supplier,variation_image,variation_name,weight;image;name;name,sku,variation_name,weight;collection,name,sku,variation_name,weight;collection,name,sku,variation_name,weight
      """
    And the following job "xlsx_catalog_modeling_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_catalog_modeling_family_import" import job page
    And I launch the import job
    And I wait for the "xlsx_catalog_modeling_family_import" job to finish
    And I should see the text "Family import"
    And I should see the text "Compute root product models data"
    And I should see the text "Compute sub product models data"
    And I should see the text "Compute products data"
    And the product model "model-braided-hat" should not have the following values "material"
    And there should only be the following job instance executed:
      | job_instance                        | times |
      | xlsx_catalog_modeling_family_import | 1     |
