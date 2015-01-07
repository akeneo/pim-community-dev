@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to be notified when I use not valid groups (not know or not variant group)

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

  Scenario: Stop the import if variant group code column is not provided
    Given the following CSV file to import:
      """
      name-en_US;description-en_US-tablet;color
      My sandal;My sandal description for locale en_US and channel tablet;white
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then I should see "Status: FAILED"
    And I should see "Variant group code must be provided"

  Scenario: Skip a variant group if it doesn't exist
    Given the following CSV file to import:
      """
      code;name-en_US;description-en_US-tablet;color
      UNKNOW;My sandal;My sandal description for locale en_US and channel tablet;white
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then I should see "Variant group \"UNKNOW\" does not exist"
    And I should see "Skipped 1"