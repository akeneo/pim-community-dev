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
    Then there should be the following family:
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
    Then there should be the following family:
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

  @jira https://akeneo.atlassian.net/browse/PIM-6127
  Scenario: Successfully raise an error when required attribute is not in the family
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;attributes;attribute_as_label;requirements-tablet;requirements-mobile
      boots;Boots;sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color;name;sku,name,description,weather_conditions,price,rating,side_view,size,color;sku,name,price,size,color
      wrong_family;Wrong Family;sku,name;name;description;description
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then I should see the text "Skipped 1"
    And I should see the text "The attribute \"description\" cannot be an attribute required for the channel \"tablet\" as it does not belong to this family: Wrong Family"
    And I should see the text "The attribute \"description\" cannot be an attribute required for the channel \"mobile\" as it does not belong to this family: Wrong Family"

  @jira https://akeneo.atlassian.net/browse/PIM-6125
  Scenario: Successfully raise an error when attribute_as_label is not in the family
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;attributes;attribute_as_label
      boots;Boots;sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color;name
      wrong_family;Wrong Family;sku;name
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then I should see the text "Skipped 1"
    And I should see the text "Property 'attribute_as_label' must belong to the family: Wrong Family"

  @jira https://akeneo.atlassian.net/browse/PIM-6125
  Scenario: Successfully raise an error when attribute_as_label is not an identifier nor a text type
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;attributes;attribute_as_label
      boots;Boots;sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color;name
      wrong_family1;Wrong Family1;sku,description;description
      wrong_family2;Wrong Family2;sku,heel_color;heel_color
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then I should see the text "Skipped 2"
    And I should see the text "Property 'attribute_as_label' only supports 'pim_catalog_text' and 'pim_catalog_identifier' attribute types for the family: Wrong Family1"
    And I should see the text "Property 'attribute_as_label' only supports 'pim_catalog_text' and 'pim_catalog_identifier' attribute types for the family: Wrong Family2"

  @jira https://akeneo.atlassian.net/browse/PIM-6124
  Scenario: Import a family with missing requirements does not remove associated family requirements
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attribute_as_label;requirements-mobile
      heels;name;manufacturer
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then there should be the following family:
      | code  | attribute_as_label | requirements-mobile | requirements-tablet                                                   |
      | heels | name               | sku,manufacturer    | sku,name,description,price,side_view,size,color,heel_color,sole_color |
