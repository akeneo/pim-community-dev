@javascript
Feature: Import group types
  In order to setup my application
  As an administrator
  I need to be able to import group types

  Scenario: Successfully update existing group type and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US
      cross_sell;Cross sell
      RELATED;Related
      """
    When the group types are imported via the job csv_footwear_group_type_import
    Then there should be the following group types:
      | code       | label-en_US |
      | cross_sell | Cross sell  |
      | RELATED    | Related     |
