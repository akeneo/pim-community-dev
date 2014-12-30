@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  # what's tested here ?
  # -----------------------------|---------------|-------------
  # TYPE                         | INVALID VALUE | NULL VALUE
  # -----------------------------|---------------|-------------
  # pim_catalog_boolean          | TODO          | TODO
  # pim_catalog_date             | TODO          | TODO
  # pim_catalog_file             | TODO          | TODO
  # pim_catalog_identifier       | N/A           | N/A
  # pim_catalog_image            | TODO          | TODO
  # pim_catalog_metric           | TODO          | TODO
  # pim_catalog_multiselect      | TODO          | TODO
  # pim_catalog_number           | TODO          | TODO
  # pim_catalog_price_collection | TODO          | TODO
  # pim_catalog_simpleselect     | TODO          | TODO
  # pim_catalog_text             | TODO          | TODO
  # pim_catalog_textarea         | TODO          | TODO

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku             | family  | categories        | size | color |
      | sandal-white-37 | sandals | winter_collection | 37   | white |
      | sandal-white-38 | sandals | winter_collection | 38   | white |
      | sandal-white-39 | sandals | winter_collection | 39   | white |
      | sandal-red-37   | sandals | winter_collection | 37   | red   |
      | sandal-red-38   | sandals | winter_collection | 38   | red   |
      | sandal-red-39   | sandals | winter_collection | 39   | red   |
    And the following product groups:
      | code   | label  | attributes  | type    | products                                                                                       |
      | SANDAL | Sandal | size, color | VARIANT | sandal-white-37, sandal-white-38, sandal-white-39, sandal-red-37, sandal-red-38, sandal-red-39 |
    And I am logged in as "Julia"

  Scenario: Skip variant group if one axis is used as values
    Given the following CSV file to import:
      """
      variant_group_code;name-en_US;description-en_US-tablet;color
      SANDAL;My sandal;My sandal description for locale en_US and channel tablet;white
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then I should see "Variant group \"SANDAL\" cannot contains axis or identifier as values (color) : [SANDAL]"

  Scenario: Skip variant group if many axis are used as values
    Given the following CSV file to import:
      """
      variant_group_code;name-en_US;description-en_US-tablet;color;size
      SANDAL;My sandal;My sandal description for locale en_US and channel tablet;white;37
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then I should see "Variant group \"SANDAL\" cannot contains axis or identifier as values (size, color) : [SANDAL]"

  Scenario: Skip variant group if identifier is used as value
    Given the following CSV file to import:
      """
      variant_group_code;name-en_US;description-en_US-tablet;sku
      SANDAL;My sandal;My sandal description for locale en_US and channel tablet;my-common-sku
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then I should see "Variant group \"SANDAL\" cannot contains axis or identifier as values (sku) : [SANDAL]"