@deprecated @javascript
Feature: Execute a product import
  In order to use existing product information
  As a product manager
  I need to be able to import products and to apply variant group values on products

  Background:
    Given the "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
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
    And the following variant group values:
      | group  | attribute   | value                | locale | scope  |
      | SANDAL | name        | My VG US name        | en_US  |        |
      | SANDAL | name        | Mon nom VG FR        | fr_FR  |        |
      | SANDAL | description | My VG US Tablet desc | en_US  | tablet |
      | SANDAL | description | Ma desc VG FR Tablet | fr_FR  | tablet |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of products with variant group values (name and desc come from variant group)
    Given the following CSV file to import:
      """
      sku;family;groups;name-en_US;size;color
      sandal-white-37;sandals;SANDAL;My prod name 37;37;white
      sandal-white-38;sandals;SANDAL;My prod name 38;38;white
      new-sandal-white-40;sandals;SANDAL;My prod name 40;40;white
      new-no-vg;sandals;;My prod name 40;40;white
      """
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then I should see "read lines 4"
    And I should see "Processed 2"
    And I should see "Created 2"
    And there should be 8 products
    And the english name of "sandal-white-38" should be "My VG US name"
    And the french name of "sandal-white-38" should be "Mon nom VG FR"
    And the english tablet description of "sandal-white-38" should be "My VG US Tablet desc"
    And the french tablet description of "sandal-white-38" should be "Ma desc VG FR Tablet"
    And the english name of "new-sandal-white-40" should be "My VG US name"
    And the french name of "new-sandal-white-40" should be "Mon nom VG FR"
    And the english tablet description of "new-sandal-white-40" should be "My VG US Tablet desc"
    And the french tablet description of "new-sandal-white-40" should be "Ma desc VG FR Tablet"
    And the english name of "new-no-vg" should be "My prod name 40"
