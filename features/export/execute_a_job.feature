Feature: Execute a job
  In order to launch an export
  As a user
  I need to be able to execute a valid export

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Fail to see the execute button of a job with validation errors
    Given the following job "footwear_product_export" configuration:
      | element   | property      | value |
      | writer    | directoryName |       |
      | writer    | fileName      |       |
    When I am on the "footwear_product_export" export job page
    Then I should not see the "Export now" link

  Scenario: Fail to launch a job with validation errors
    Given the following job "footwear_product_export" configuration:
      | element   | property      | value |
      | writer    | directoryName |       |
      | writer    | fileName      |       |
    When I launch the "footwear_product_export" export job
    Then I should not see "The export is running."
    And I should not see "An error occured during the export execution."

  Scenario: Successfully launch a valid job
    Given the following product:
      | sku       | family | categories        |
      | boots-001 | boots  | winter_collection |
    And the following product values:
      | product   | attribute | value          | locale |
      | boots-001 | Name      | Boots 1        | en_US  |
      | boots-001 | Price     | 20 EUR, 25 USD |        |
      | boots-001 | Size      | 40             |        |
      | boots-001 | Color     | black          |        |
    And I launched the completeness calculator
    And I am on the "footwear_product_export" export job page
    When I launch the "footwear_product_export" export job
    And I wait for the job to finish
    Then I should see "Execution details"
    And file "/tmp/product_export/product_export.csv" should exist
    And an email to "admin@example.com" should have been sent
