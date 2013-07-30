@javascript
Feature: Browse exports
  In order to view the list of export jobs that have been created
  As an user
  I need to be able to view a list of them

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                       | type   |
      | Akeneo    | product_export   | acme_product_export   | Product export for Acme.com | export |
      | Akeneo    | attribute_export | acme_attribute_export | Attribute export            | export |
      | Akeneo    | product_export   | foo_product_export    | Product export for foo      | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com | import |
    Given I am logged in as "admin"

  Scenario: Successfully display all the export jobs
    Given I am on the exports index page
    And the grid should contain 3 elements
    And the grid should contains the elements "acme_product_export", "acme_attribute_export" and "foo_product_export"
    And the grid should not contains the elements "acme_product_import"
    And the column "connector" of the row "acme_product_export" should contains the value "Akeneo"
    And I should see the filters "Code", "Label", "Job", "Connector" and "Status"

  Scenario: Successfully delete an export job
    Given I am on the exports index page
    And the grid should contain 3 elements
    When I delete the "foo_product_export" job
    Then I confirm on the grid modal window
    Then the grid should contain 2 elements
    And the grid should contains the elements "acme_product_export" and "acme_attribute_export"
    And the grid should not contains the elements "foo_product_export" and "acme_product_import"

  Scenario: Successfully cancel the deletion of an export job
    Given I am on the exports index page
    And the grid should contain 3 elements
    When I delete the "foo_product_export" job
    Then I cancel on the grid modal window
    Then the grid should contain 3 elements
    And the grid should contains the elements "acme_product_export", "acme_attribute_export" and "foo_product_export"
    And the grid should not contains the elements "acme_product_import"