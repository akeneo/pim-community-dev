@javascript
Feature: Display only logged user's jobs execution in last executions job view
  In order to have an overview of last job executions
  As a regular user
  I need to be able to browse the last job executions

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;sort_order;label-fr_FR;max_characters;number_min;number_max;decimals_allowed;negative_allowed;max_file_size
      pim_catalog_identifier;sku;SKU;info;1;1;;;;;0;0;1;;;;;;;
      """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_attribute_import" export job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    And I am on the "csv_footwear_attribute_import" export job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    And I logout
    And I am logged in as "admin"
    And I am on the "csv_footwear_attribute_import" export job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    And I am on the job tracker page

  Scenario: Only view last executions of user
    Given I am on the imports grid
    When I click on the "CSV footwear attribute import" row
    Then the grid should contain 1 element
