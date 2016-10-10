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
    And I expand the "Description" attribute
    And I fill in the following information:
      | tablet Description | original description tablet |
      | mobile Description | original description mobile |
    And I save the variant group
    And the following CSV file to import:
      """
        label-en_US;axis;code;description-en_US-tablet;type
        Sandal;color,size;SANDAL;"new description tablet";VARIANT
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    And I am on the "SANDAL" variant group page
    And I visit the "Attributes" tab
    And I expand the "Description" attribute
    Then the field tablet Description should contain "new description tablet"
    And the field mobile Description should contain "original description mobile"
