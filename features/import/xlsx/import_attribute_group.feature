@javascript
Feature: Import attribute groups
  In order to setup my application
  As an administrator
  I need to be able to import attribute groups

  Scenario: Successfully import new attribute group in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;6
      """
    And the following job "xlsx_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_attribute_group_import" job to finish
    Then there should be the following attribute groups:
      | code          | label-en_US   | attributes                           | sort_order |
      | manufacturing | Manufacturing | lace_fabric,manufacturer,sole_fabric | 6          |
