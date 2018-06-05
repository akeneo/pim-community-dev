@javascript
Feature: Export currencies
  In order to be able to access and modify currencies data outside PIM
  As an administrator
  I need to be able to export currencies

  Scenario: Successfully export currencies
    Given a "default" catalog configuration
    And the following jobs:
      | connector            | type   | alias               | code                | label           |
      | Akeneo CSV Connector | export | csv_currency_export | csv_currency_export | currency export |
    And the following job "csv_currency_export" configuration:
      | filePath | %tmp%/currency_export/currency_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_currency_export" export job page
    When I launch the export job
    And I wait for the "csv_currency_export" job to finish
    Then I should see the text "Read 6"
    And I should see the text "Written 6"
    And exported file of "csv_currency_export" should contain:
    """
    code;activated
    USD;1
    EUR;1
    ADP;0
    AED;0
    AFA;0
    AFN;0
    """
