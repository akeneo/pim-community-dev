@javascript
Feature: Export categories
  In order to be able to access and modify category data outside PIM
  As a user
  I need to be able to import and export categories

  Background:
    Given the following jobs:
      | connector            | alias           | code                 | label                        | type   |
      | Akeneo CSV Connector | category_export | acme_category_export | Category export for Acme.com | export |
      | Akeneo CSV Connector | category_import | acme_category_import | Category import for Acme.com | import |
    And I am logged in as "admin"

  Scenario: Successfully export categories
    Given the following categories:
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
    Then file "/tmp/category_export.csv" should contain 6 rows
    And the category order in the file "/tmp/category_export.csv" should be following:
      | default     |
      | computers   |
      | laptops     |
      | hard_drives |
      | pc          |

  Scenario: Succesfully import categories
    Given the following file to import:
    """
    code;parent;dynamic;label
    default;;;
    computers;;;en_US:Computers
    laptops;computers;;en_US:Laptops
    hard_drives;laptops;;"en_US:Hard drives"
    pc;computers;;en_US:PC
    """
    And the following job "acme_category_import" configuration:
      | element | property | value                |
      | reader  | filePath | {{ file to import }} |
    When I am on the "acme_category_import" import job page
    And I launch the import job
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |
