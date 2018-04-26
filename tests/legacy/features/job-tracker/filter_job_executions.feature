@javascript
Feature: Filter jobs execution in job tracker
  In order to easily find some jobs
  As a regular user
  I need to be able to filter the job executions in the job tracker

  @jira https://akeneo.atlassian.net/browse/PIM-7168
  Scenario Outline: Successfully filter job executions
    Given a "default" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      sku
      my_sku
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_default_product_import" export job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish
    And I am on the job tracker page
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter | operator    | value     | result                     | count |
      | status | is equal to | Completed | CSV default product import | 1     |
      | status | is equal to | Started   |                            | 0     |
