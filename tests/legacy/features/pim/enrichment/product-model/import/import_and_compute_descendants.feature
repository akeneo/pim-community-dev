@javascript
Feature: Create product models through CSV import and update their descendants
  In order to import product model
  As a catalog manager
  I need to be able to import product models and update their descendants

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following CSV file to import:
      """
      code;family_variant;parent;supplier;price-EUR;care_instructions;wash_temperature;color;composition
      model-tshirt-divided;clothing_color_size;;zaro;20;Machine-washable;400;;
      model-tshirt-divided-navy-blue;clothing_color_size;model-tshirt-divided;;;;;navy_blue;100% cotton
      """
    And the following job "csv_catalog_modeling_product_model_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"

  @critical
  Scenario: Successfully compute products' completenesses of the product models
    Given I am on the "tshirt-divided-navy-blue-m" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel   | locale | state   | missing_values | ratio |
      | ecommerce | de_DE  | warning | 4              | 63%   |
      | ecommerce | en_US  | warning | 3              | 72%   |
      | ecommerce | fr_FR  | warning | 4              | 63%   |
    When I am on the "csv_catalog_modeling_product_model_import" import job page
    And I launch the import job
    And I wait for the "csv_catalog_modeling_product_model_import" job to finish
    Then I should see the text "processed 1"
    When I am on the "tshirt-divided-navy-blue-m" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel   | locale | state   | missing_values | ratio |
      | ecommerce | de_DE  | warning | 1              | 90%   |
      | ecommerce | en_US  | success | 0              | 100%  |
      | ecommerce | fr_FR  | warning | 1              | 90%   |
