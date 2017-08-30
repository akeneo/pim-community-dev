@javascript
Feature: Import variant products
  In order import my variant product
  As a catalog manager
  I need to be able to import product models with valid data

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Skip a variant product if its family is different than its parent
    Given the following root product model "code-001" with the variant family clothing_color_size
    And the following sub product model "code-002" with "code-001" as parent
    And the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;shoes;master_men;EAN;SKU-001;100;GRAM;m
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "The variant product family must be the same than its parent"

  Scenario: Successfully skip a variant product if the parent doesn't exist
    Given the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;clothing;master_men;EAN;SKU-001;100;GRAM;m
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Property \"parent\" expects a valid parent code. The parent product model does not exist, \"code-001\" given."
    And the invalid data file of "csv_default_product_import" should contain:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      code-001;clothing;master_men;EAN;SKU-001;100;GRAM;m
      """

  Scenario: Successfully skip a variant product if the parent product model is a root product model and the family variant has 2 levels
    Given the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;m
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Parent of the variant product \"apollon_blue_medium\" cannot have product models as children, only variant products"
    And the invalid data file of "csv_default_product_import" should contain:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;m
      """

  Scenario: Successfully skip a variant product if there is no value for the variant axes defined in the family variant for the last level
    Given the following CSV file to import:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon_blue;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    Then I should see the text "Status: Completed"
    And I should see the text "skipped 1"
    And I should see the text "Attribute \"size\" cannot be empty, as it is defined as an axis for this entity: apollon_blue_medium"
    And the invalid data file of "csv_default_product_import" should contain:
      """
      parent;family;categories;ean;sku;weight;weight-unit;size
      apollon_blue;clothing;master_men;EAN;apollon_blue_medium;100;GRAM;
      """
