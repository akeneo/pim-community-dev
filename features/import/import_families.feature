@javascript
Feature: Import families
  In order to reuse the families of my products
  As a product manager
  I need to be able to import families

  Scenario: Successfully import new family in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
      tractors;sku,name,manufacturer;name;manufacturer;manufacturer;Tractors
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then there should be the following families:
      | code     | attributes            | attribute_as_label | requirements-mobile | requirements-tablet | label-en_US |
      | tractors | sku,name,manufacturer | name               | sku,manufacturer    | sku,manufacturer    | Tractors    |

  Scenario: Successfully update existing family and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
      heels;sku,name,manufacturer,heel_color;name;manufacturer;manufacturer,heel_color;Heels
      tractors;sku,name,manufacturer;name;;;Tractor
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then there should be the following families:
      | code     | attributes                       | attribute_as_label | requirements-mobile | requirements-tablet         | label-en_US |
      | heels    | sku,name,manufacturer,heel_color | name               | sku,manufacturer    | sku,heel_color,manufacturer | Heels       |
      | tractors | sku,name,manufacturer            | name               | sku                 | sku                         | Tractor     |

  Scenario: Successfully import new family in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
      tractors;sku,name,manufacturer;name;manufacturer;manufacturer;Tractors
      """
    And the following job "xlsx_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_family_import" job to finish
    Then there should be the following families:
      | code     | attributes            | attribute_as_label | requirements-mobile | requirements-tablet | label-en_US |
      | tractors | sku,name,manufacturer | name               | sku,manufacturer    | sku,manufacturer    | Tractors    |

  @jira https://akeneo.atlassian.net/browse/PIM-6107
  Scenario: Import an empty label should display the family code on the product datagrid
    Given the "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | pretty-shoe | heels  |
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
      heels;sku,name,manufacturer,heel_color;name;manufacturer;manufacturer,heel_color;
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    And I am on the products page
    Then the row "pretty-shoe" should contain:
      | column | value   |
      | Family | [heels] |
