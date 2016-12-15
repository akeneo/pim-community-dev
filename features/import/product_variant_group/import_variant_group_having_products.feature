@javascript
Feature: Execute an import of variant group having matching products
  In order to update existing product information
  As a product manager
  I need to be able to import variant group having matching products

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
      | code    | label   | axis        | type    | products                                          |
      | SANDAL  | Sandal  | size, color | VARIANT | sandal-white-37, sandal-white-38, sandal-white-39 |
      | SANDAL2 | Sandal2 | size, color | VARIANT | sandal-red-37, sandal-red-38, sandal-red-39       |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5990
  Scenario: Import a variant group two times changes nothing
    Given the following CSV file to import:
      """
      code;axis;type;name-en_US;description-en_US-tablet
      SANDAL;size,color;VARIANT;Sandal;A description
      SANDAL2;size,color;VARIANT;Sandal2;Another description
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then I should see the text "Updated Products 6"
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then I should see the text "Updated Products 6"
