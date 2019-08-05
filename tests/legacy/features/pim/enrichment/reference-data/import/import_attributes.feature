@javascript
Feature: Import attributes
  In order to reuse the attributes of my products
  As a product manager
  I need to be able to import attributes

  @critical
  Scenario: Successfully import attributes with reference data
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
    """
    type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;reference_data_name;sort_order
    pim_catalog_text;shortname;Shortname;info;0;1;1;0;;;;;0
    pim_reference_data_simpleselect;mycolor;My color;info;0;1;0;0;;;;color;0
    pim_reference_data_multiselect;myfabrics;My fabrics;info;0;1;0;0;;;;fabrics;0

    """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then there should be the following attributes:
      | type                            | code      | label-en_US | group | unique | useable_as_grid_filter | localizable | scopable | allowed_extensions | metric_family | default_metric_unit | reference_data_name | sort_order |
      | pim_catalog_text                | shortname | Shortname   | info  | 0      | 1                      | 1           | 0        |                    |               |                     |                     | 0          |
      | pim_reference_data_simpleselect | mycolor   | My color    | info  | 0      | 1                      | 0           | 0        |                    |               |                     | color               | 0          |
      | pim_reference_data_multiselect  | myfabrics | My fabrics  | info  | 0      | 1                      | 0           | 0        |                    |               |                     | fabrics             | 0          |

  Scenario: Fail to import attributes with unregistered reference data
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
    """
    type;code;label-en_US;group;unique;useable_as_grid_filter;localizable;scopable;allowed_extensions;metric_family;default_metric_unit;reference_data_name
    pim_catalog_text;shortname;Shortname;info;0;1;1;0;;;;
    pim_reference_data_simpleselect;mycolor;My color;info;0;1;0;0;;;;test
    pim_reference_data_multiselect;myfabrics;My fabrics;info;0;1;0;0;;;;notfound

    """
    And the following job "csv_footwear_attribute_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_import" job to finish
    Then I should see the text "Reference data \"test\" does not exist."
