@javascript
Feature: Import attributes
  In order to have validation errors with assets collection in attribute import
  As a product manager
  I need to be able to show validation errors

  Scenario: Successfully show validation errors on assets collection attributes import
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      type;code;label-en_US;group;unique;useable_as_grid_filter;allowed_extensions;metric_family;default_metric_unit;reference_data_name;localizable;scopable;sort_order
      pim_assets_collection;scopable_localizable_attribute;;info;0;0;;;;assets;1;1;1
      pim_assets_collection;scopable_attribute;;info;0;0;;;;assets;0;1;2
      pim_assets_collection;localizable_attribute;;info;0;0;;;;assets;1;0;3
      pim_assets_collection;valid_attribute;;info;0;0;;;;assets;0;0;4
      pim_assets_collection;empty_attribute;;info;0;0;;;;assets;;;5
      pim_catalog_text;other_attribute;;info;0;0;;;;;1;1;6
      """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then I should see "skipped 3"
    Then I should see "The assets collection attribute can not be scopable nether localizable: [scopable_localizable_attribute]"
    And I should see "The assets collection attribute can not be scopable nether localizable: [scopable_attribute]"
    And I should see "The assets collection attribute can not be scopable nether localizable: [localizable_attribute]"

  Scenario: Successfully import attribute with read only parameter
    Given the "clothing" catalog configuration
    And the following products:
      | sku       | family  |
      | my-jacket | jackets |
    And I am logged in as "Julia"
    And I am on the attributes page
    And the following CSV file to import:
    """
    type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;reference_data_name;is_read_only;sort_order
    pim_catalog_text;shortname;Shortname;info;0;1;1;0;;;;;1;2
    """
    And the following job "csv_clothing_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_attribute_import" job to finish
    Then there should be the following attributes:
      | type | code       | label-en_US  | group   | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit | reference_data_name | is_read_only | sort_order
      | text | shortname  | Shortname    | info    | 0      | 1                      | 1           | 0        |                    |               |                     |                     | 1            | 2
