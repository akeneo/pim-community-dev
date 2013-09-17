Feature: Export categories
  In order to be able to access and modify category data outside PIM
  As a user
  I need to be able to import and export categories

  Background:
  Given the following categories:
    | code        | title       | parent    |
    | computers   | Computers   |           |
    | laptops     | Laptops     | computers |
    | hard_drives | Hard drives | laptops   |
    | pc          | PC          | computers |
  Given the following jobs:
    | connector            | alias           | code                 | label                        | type   |
    | Akeneo CSV Connector | category_export | acme_category_export | Category export for Acme.com | export |
    | Akeneo CSV Connector | category_import | acme_category_import | Category import for Acme.com | import |
  And the following job "acme_category_export" configuration:
    | element | property | value                    |
    | writer  | path     | /tmp/category_export.csv |
  And the following job "acme_category_import" configuration:
    | element | property | value                    |
    | reader  | filePath | /tmp/category_import.csv |
  And I am logged in as "admin"

  Scenario: Successfully import and export categories
    Given I am on the "acme_category_export" export job page
    When I launch the export job
    Then I should see "The export has been successfully executed."
    And file "/tmp/category_export.csv" should contain 5 rows
    And the category order in the file "/tmp/category_export.csv" should be following:
     | computers   |
     | laptops     |
     | hard_drives |
     | pc          |
    Then I copy the file "/tmp/category_export.csv" to "/tmp/category_import.csv"
    And I move the row 2 to row 4 in the file "/tmp/category_export.csv"
    When I am on the "acme_category_import" import job page
    And I launch the import job
    Then I should see "The import has been successfully executed."
    And file "/tmp/category_export.csv" should contain 5 rows
    And the category order in the file "/tmp/category_export.csv" should be following:
     | computers   |
     | hard_drives |
     | pc          |
     | laptops     |
