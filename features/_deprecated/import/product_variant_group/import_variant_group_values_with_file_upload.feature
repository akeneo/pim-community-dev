@deprecated @javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values through file upload

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
      | code   | label  | axis        | type    | products                                                                                       |
      | SANDAL | Sandal | size, color | VARIANT | sandal-white-37, sandal-white-38, sandal-white-39, sandal-red-37, sandal-red-38, sandal-red-39 |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of variant group values through file upload
    Given the following CSV file to import:
      """
      code;type;name-en_US;description-en_US-tablet
      SANDAL;VARIANT;My sandal;My sandal description for locale en_US and channel tablet
      """

    And the following job "footwear_variant_group_import" configuration:
      | uploadAllowed | yes |
    When I am on the "footwear_variant_group_import" import job page
    And I upload and import the file "%file to import%"
    And I wait for the "footwear_variant_group_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | name-en_US               | My sandal                                                 |
      | description-en_US-tablet | My sandal description for locale en_US and channel tablet |
    And the product "sandal-white-38" should have the following value:
      | name-en_US               | My sandal                                                 |
      | description-en_US-tablet | My sandal description for locale en_US and channel tablet |
