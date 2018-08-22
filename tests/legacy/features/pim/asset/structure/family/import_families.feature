@javascript
Feature: Import families
  In order to reuse the families of my products
  As a product manager
  I need to be able to import families

  Scenario: Successfully fail when attribute_as_image is invalid
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;attributes;attribute_as_label;attribute_as_image
      wrong_family1;sku,name;sku;name
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_family_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then I should see the text "Skipped 1"
    And I should see the text "Property \"attribute_as_image\" only supports \"pim_catalog_image\", \"pim_assets_collection\" attribute type for the family: [wrong_family1]"
