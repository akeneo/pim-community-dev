Feature: Import group types
  In order to setup my application
  As an administrator
  I need to be able to import group types

  Scenario: Successfully import new group type in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US
      cross_sell;Cross sell
      """
    When I import it via the job "csv_footwear_group_type_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following group types:
      | code       | label-en_US |
      | cross_sell | Cross sell  |

  Scenario: Successfully update existing group type and add a new one
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US
      cross_sell;Cross sell
      RELATED;Related
      """
    When I import it via the job "csv_footwear_group_type_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following group types:
      | code       | label-en_US |
      | cross_sell | Cross sell  |
      | RELATED    | Related     |
