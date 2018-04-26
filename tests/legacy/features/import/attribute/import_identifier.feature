@javascript
Feature: Import identifier attributes
  In order to reuse the attributes of my products
  As a product manager
  I need to be able to import identifier attributes

  @jira https://akeneo.atlassian.net/browse/PIM-6018
  Scenario: Successfully import attributes in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;sort_order;label-fr_FR;max_characters;number_min;number_max;decimals_allowed;negative_allowed;max_file_size
      pim_catalog_identifier;sku;SKU;info;1;0;;;;;0;0;1;;;;;;;
      """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then I should see the text "skipped 1"
    And I should see the text "\"sku\" is an identifier attribute, it must be usable as grid filter"
