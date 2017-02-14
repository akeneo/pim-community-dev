@javascript
Feature: Execute an import of variant group having matching products
  In order to update existing product information
  As a product manager
  I need to be able to import variant group having matching products

  Background:
    Given the "footwear" catalog configuration
    And the following variant groups:
      | code    | label-en_US | axis       | type    |
      | SANDAL  | Sandal      | size,color | VARIANT |
      | SANDAL2 | Sandal2     | size,color | VARIANT |
    And the following products:
      | sku             | family  | categories        | size | color | groups  |
      | sandal-white-37 | sandals | winter_collection | 37   | white | SANDAL  |
      | sandal-white-38 | sandals | winter_collection | 38   | white | SANDAL  |
      | sandal-white-39 | sandals | winter_collection | 39   | white | SANDAL  |
      | sandal-red-37   | sandals | winter_collection | 37   | red   | SANDAL2 |
      | sandal-red-38   | sandals | winter_collection | 38   | red   | SANDAL2 |
      | sandal-red-39   | sandals | winter_collection | 39   | red   | SANDAL2 |
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
