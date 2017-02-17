@javascript
Feature: Execute an import with axis as data
  In order to update existing product information
  As a product manager
  I need to be able to be notified when I use not valid attributes as identifier or axis

  Background:
    Given the "footwear" catalog configuration
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | SANDAL | Sandal      | size,color | VARIANT |
    And the following products:
      | sku             | family  | categories        | size | color | groups |
      | sandal-white-37 | sandals | winter_collection | 37   | white | SANDAL |
      | sandal-white-38 | sandals | winter_collection | 38   | white | SANDAL |
      | sandal-white-39 | sandals | winter_collection | 39   | white | SANDAL |
      | sandal-red-37   | sandals | winter_collection | 37   | red   | SANDAL |
      | sandal-red-38   | sandals | winter_collection | 38   | red   | SANDAL |
      | sandal-red-39   | sandals | winter_collection | 39   | red   | SANDAL |
    And I am logged in as "Julia"

  Scenario: Skip variant group if one axis is used as values
    Given the following CSV file to import:
      """
      code;type;name-en_US;description-en_US-tablet;color
      SANDAL;VARIANT;My sandal;My sandal description for locale en_US and channel tablet;white
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then I should see:
    """
    Variant group "SANDAL" cannot contain values for axis or unique attributes: "color": [SANDAL]
    """
    And I should see "Skipped 1"

  Scenario: Skip variant group if many axis are used as values
    Given the following CSV file to import:
      """
      code;type;name-en_US;description-en_US-tablet;color;size
      SANDAL;VARIANT;My sandal;My sandal description for locale en_US and channel tablet;white;37
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then I should see:
    """
    Variant group "SANDAL" cannot contain values for axis or unique attributes: "size", "color": [SANDAL]
    """
    And I should see "Skipped 1"

  Scenario: Skip variant group if identifier is used as value
    Given the following CSV file to import:
      """
      code;type;name-en_US;description-en_US-tablet;sku
      SANDAL;VARIANT;My sandal;My sandal description for locale en_US and channel tablet;my-common-sku
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then I should see:
    """
    Variant group "SANDAL" cannot contain values for axis or unique attributes: "sku": [SANDAL]
    """
    And I should see "Skipped 1"
