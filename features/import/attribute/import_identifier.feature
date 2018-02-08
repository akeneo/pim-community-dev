Feature: Import identifier attributes
  In order to reuse the attributes of my products
  As a product manager
  I need to be able to import identifier attributes

  @jira https://akeneo.atlassian.net/browse/PIM-6018
  Scenario: Successfully import attributes in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;sort_order;label-fr_FR;max_characters;number_min;number_max;decimals_allowed;negative_allowed;max_file_size
      pim_catalog_identifier;sku;SKU;info;1;0;;;;;0;0;1;;;;;;;
      """
    When I import it via the job "csv_footwear_attribute_import" as "Julia"
    And I wait for this job to finish
    Then I should see the text "skipped 1"
    And I should see the text "\"sku\" is an identifier attribute, it must be usable as grid filter"
