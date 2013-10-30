Feature: Export categories
  In order to be able to access and modify category data outside PIM
  As Julia
  I need to be able to import and export categories

  @javascript
  Scenario: Successfully export categories
    Given the following jobs:
      | connector            | alias           | code                 | label                        | type   |
      | Akeneo CSV Connector | category_export | acme_category_export | Category export for Acme.com | export |
    And I am logged in as "Julia"
    And the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |
    And the following job "acme_category_export" configuration:
      | element | property      | value               |
      | writer  | directoryName | /tmp/               |
      | writer  | fileName      | category_export.csv |
    And I am on the "acme_category_export" export job page
    When I launch the export job
    And I wait for the job to finish
    Then file "/tmp/category_export.csv" should contain 6 rows
    And the category order in the file "/tmp/category_export.csv" should be following:
      | default     |
      | computers   |
      | laptops     |
      | hard_drives |
      | pc          |
