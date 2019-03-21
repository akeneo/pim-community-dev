@javascript
Feature: Import attribute groups
  In order to setup my application
  As an administrator
  I need to be able to import attribute groups

  @critical
  Scenario: Successfully set default permission on import
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;6
      """
    And the following job "csv_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_group_import" job to finish
    And I am on the "manufacturing" attribute group page
    When I visit the "Permissions" tab
    Then I should see the text "Allowed to view attributes All"
    And I should see the text "Allowed to edit attributes All"
