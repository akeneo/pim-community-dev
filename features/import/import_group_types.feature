@javascript
Feature: Import group types
  In order to setup my application
  As an administrator
  I need to be able to import group types

  Scenario: Successfully import new group type in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US
      cross_sell;Cross sell
      """
    And the following job "csv_footwear_group_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_group_type_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_group_type_import" job to finish
    Then there should be the following group types:
      | code       | label-en_US |
      | cross_sell | Cross sell  |

  Scenario: Successfully update existing group type and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US
      cross_sell;Cross sell
      RELATED;Related
      """
    And the following job "csv_footwear_group_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_group_type_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_group_type_import" job to finish
    Then there should be the following group types:
      | code       | label-en_US |
      | cross_sell | Cross sell  |
      | RELATED    | Related     |
