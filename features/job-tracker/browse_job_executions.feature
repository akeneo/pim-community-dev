@javascript
Feature: Display jobs execution in job tracker
  In order to have an overview of last job operations
  As a regular user
  I need to be able to browse the job executions in the job tracker

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the exports page
    And I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    And I logout
    And I am logged in as "admin"
    And I am on the "csv_footwear_category_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_category_export" job to finish
    And I am on the job tracker page

  @jira https://akeneo.atlassian.net/browse/PIM-6140
  Scenario Outline: Successfully filter job executions
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter | operator    | value  | result                                                    | count |
      | user   | is equal to | Julia  | CSV footwear product export                               | 1     |
      | type   | is equal to | import |                                                           | 0     |
      | type   | is equal to | export | CSV footwear product export, CSV footwear category export | 2     |

  Scenario: Successfully search on label
    When I search "footwear product export"
    Then the grid should contain 1 element
    And I should see entity CSV footwear product export
