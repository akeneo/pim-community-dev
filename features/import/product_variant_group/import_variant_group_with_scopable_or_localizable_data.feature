@javascript
Feature: Execute an import with scopable or localizable data
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  Background:
    Given the "footwear" catalog configuration
    And the following product groups:
      | code   | label  | axis        | type    |
      | SANDAL | Sandal | size, color | VARIANT |
    And I am logged in as "Julia"

  Scenario: Avoid data loss when importing variant group localizable/scopable values
    Given I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Description
    And I change the Description for scope tablet and locale en_US to "original description tablet"
    And I change the Description for scope mobile and locale en_US to "original description mobile"
    And I save the variant group
    And the following CSV file to import:
      """
        label-en_US;axis;code;description-en_US-tablet;type
        Sandal;color,size;SANDAL;"new description tablet";VARIANT
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    And I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    Then the field Description for locale "en_US" and scope "tablet" should contain "new description tablet"
    And the field Description for locale "en_US" and scope "mobile" should contain "original description mobile"
