@javascript
Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  Scenario: Successfully import new family
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
      tractors;sku,name,manufacturer;name;manufacturer;manufacturer;Tractors
      """
    And the following job "footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_family_import" import job page
    And I launch the import job
    And I wait for the "footwear_family_import" job to finish
    Then there should be the following families:
      | code     | attributes            | attribute_as_label | requirements-mobile | requirements-tablet | label-en_US |
      | tractors | sku,name,manufacturer | name               | sku,manufacturer    | sku,manufacturer    | Tractors    |

  Scenario: Successfully update existing family
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
      heels;sku,name,manufacturer,heel_color;name;manufacturer;manufacturer,heel_color;Heels
      """
    And the following job "footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_family_import" import job page
    And I launch the import job
    And I wait for the "footwear_family_import" job to finish
    Then there should be the following families:
      | code  | attributes                       | attribute_as_label | requirements-mobile | requirements-tablet         | label-en_US |
      | heels | sku,name,manufacturer,heel_color | name               | sku,manufacturer    | sku,heel_color,manufacturer | Heels       |
