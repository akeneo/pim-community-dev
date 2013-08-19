@javascript
Feature: Browse exports
  In order to view the list of export jobs that have been created
  As a user
  I need to be able to view a list of them

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                       | type   |
      | Akeneo    | product_export   | acme_product_export   | Product export for Acme.com | export |
      | Akeneo    | attribute_export | acme_attribute_export | Attribute export            | export |
      | Akeneo    | product_export   | foo_product_export    | Product export for foo      | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com | import |
    Given I am logged in as "admin"

  Scenario: Successfully display the export jobs
    Given I am on the exports page
    Then the grid should contain 3 elements
    And I should see export profiles "acme_product_export", "acme_attribute_export" and "foo_product_export"
    And I should not see export profile "acme_product_import"
    And the column "connector" of the row "acme_product_export" should contain the value "Akeneo"

  Scenario: Successfully display columns
    Given I am on the exports page
    Then I should see the columns Code, Label, Job, Connector and Status
