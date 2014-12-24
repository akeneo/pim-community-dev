@javascript
Feature: Execute a job
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

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

  Scenario: Successfully import a csv file of variant group values
    Given the following file to import:
      """
      variant_group_code;name-en_US;description-en_US-tablet
      SANDAL;My sandal;My sandal description for locale en_US and channel tablet
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the english tablet name of "sandal-white-37" should be "My sandal"
    And the english tablet description of "sandal-white-37" should be "My sandal description for locale en_US and channel tablet"
    And the english tablet name of "sandal-white-38" should be "My sandal"
    And the english tablet description of "sandal-white-38" should be "My sandal description for locale en_US and channel tablet"
