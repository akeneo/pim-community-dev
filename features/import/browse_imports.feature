@javascript
Feature: Browse imports
  In order to view the list of import jobs that have been created
  As a user
  I need to be able to view a list of them

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                        | type   |
      | Akeneo    | product_export   | acme_product_export   | Product export for Acme.com  | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com  | import |
      | Akeneo    | category_import  | acme_category_import  | Category import for Acme.com | import |
      | Akeneo    | category_import  | foo_category_import   | Category import for foo      | import |
    Given I am logged in as "admin"

  Scenario: Successfully display all the import jobs
    Given I am on the imports page
    Then the grid should contain 3 element
    And I should see import profiles acme_product_import, acme_category_import and foo_category_import
    And I should not see import profiles acme_product_export
    And the column "connector" of the row "acme_product_import" should contain the value "Akeneo"

  Scenario: Successfully display columns
    Given I am on the imports page
    Then I should see the columns Code, Label, Job, Connector and Status
