@javascript
Feature: Stop invalid imports of product model
  In order to import correct product model
  As a catalog manager
  I need to be able to stop an import with bad file structure

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Skip import with a unexpected field
    Given the following CSV file to import:
      """
      code;parent;family_variant;comment
      code-001;;clothing_color_size;"my comment"
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "Status: FAILED"
    And I should see the text " The field \"comment\" does not exist"
