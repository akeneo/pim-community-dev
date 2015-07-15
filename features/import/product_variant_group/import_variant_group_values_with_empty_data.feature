@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku             | family  | categories        | size | color | name-en_US |
      | sandal-white-37 | sandals | winter_collection | 37   | white | old name   |
      | sandal-white-38 | sandals | winter_collection | 38   | white | old name   |
      | sandal-white-39 | sandals | winter_collection | 39   | white | old name   |
      | sandal-red-37   | sandals | winter_collection | 37   | red   | old name   |
      | sandal-red-38   | sandals | winter_collection | 38   | red   | old name   |
      | sandal-red-39   | sandals | winter_collection | 39   | red   | old name   |
    And the following product groups:
      | code    | label   | axis        | type    | products                                          |
      | SANDAL  | Sandal  | size, color | VARIANT | sandal-white-37, sandal-white-38, sandal-white-39 |
      | SANDAL2 | Sandal2 | size, color | VARIANT | sandal-red-37, sandal-red-38, sandal-red-39       |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file with values for one variant group and empty values for the other
    Given the following CSV file to import:
      """
      code;type;name-en_US
      SANDAL;VARIANT;My new name
      SANDAL2;VARIANT;
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 2"
    And I should see "Processed 2"
    And I should see "Updated products 3"
    And the product "sandal-white-37" should have the following value:
      | name-en_US | My new name |
    And the product "sandal-red-37" should have the following value:
      | name-en_US | old name |
