Feature: Import variant products
  In order import my variant product
  As a catalog manager
  I need to be able to import product models with valid data

  Background:
    Given the "catalog_modeling" catalog configuration

  Scenario: When we import a variant product without a family, then its parent's family is assigned to it.
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;categories;ean;sku;weight;weight-unit;size
      code-002;master_men;EAN;SKU-001;100;GRAM;m
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    And the family of "SKU-001" should be "clothing"

  Scenario: Import variant product by ignoring attributes that are not part of the family
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size;color
      code-002;clothing;master_men;EAN;SKU-001;100;GRAM;m;red
      """
    When the products are imported via the job csv_catalog_modeling_product_import
    Then the variant product "SKU-001" should not have the following values:
      | color |

  @javascript
  Scenario: Skip a variant product if its family is different than its parent
    Given I am logged in as "Julia"
    And the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;shoes;master_men;EAN;SKU-001;100;GRAM;m
      """
    And the following job "csv_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "The variant product family must be the same than its parent"

  @javascript
  Scenario: Successfully skip a variant product if the parent doesn't exist
    Given I am logged in as "Julia"
    And  the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;clothing;master_men;EAN;SKU-001;100;GRAM;m
      """
    And the following job "csv_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Property \"parent\" expects a valid parent code. The parent product model does not exist, \"code-001\" given."
    And the invalid data file of "csv_catalog_modeling_product_import" should contain:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;clothing;master_men;EAN;SKU-001;100;GRAM;m
      """

  @javascript
  Scenario: Successfully skip a variant product if the parent product model is a root product model and the family variant has 2 levels
    Given I am logged in as "Julia"
    And  the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;m
      """
    And the following job "csv_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "The variant product \"apollon_blue_medium\" cannot have product model \"apollon\" as parent, (this product model can only have other product models as children)"
    And the invalid data file of "csv_catalog_modeling_product_import" should contain:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;m
      """

  @javascript
  Scenario: Successfully skip variant products if there are no values for their variant axes as defined in the family variant
    Given I am logged in as "Julia"
    And  the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon_blue;clothing;master_men;12345;apollon_blue_medium;100;GRAM;
      apollon_blue;clothing;master_men;67890;apollon_blue_large;100;GRAM;
      """
    And the following job "csv_catalog_modeling_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 2"
    And I should not see the text "Cannot set value \"\" for the attribute axis \"size\""
    But I should see the text "Attribute \"size\" cannot be empty, as it is defined as an axis for this entity: apollon_blue_medium"
    And the invalid data file of "csv_catalog_modeling_product_import" should contain:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon_blue;clothing;master_men;12345;apollon_blue_medium;100;GRAM;
      apollon_blue;clothing;master_men;67890;apollon_blue_large;100;GRAM;
      """
