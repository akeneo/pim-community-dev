Feature: Import group types
  In order to setup my application
  As an administrator
  I need to be able to import group types

  Scenario: Successfully import new group type in XLSX
    Given the "footwear" catalog configuration
    And the following XLSX file to import:
      """
      code;label-en_US
      cross_sell;Cross sell
      """
    When I import it via the job "xlsx_footwear_group_type_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following group types:
      | code       | label-en_US |
      | cross_sell | Cross sell  |
