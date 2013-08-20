@javascript
Feature: Filter import profiles
  In order to filter import profiles in the catalog
  As a user
  I need to be able to filter import profiles in the catalog

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                        | type   |
      | Akeneo    | product_export   | acme_product_export   | Product export for Acme.com  | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com  | import |
      | Akeneo    | category_import  | acme_category_import  | Category import for Acme.com | import |
      | Akeneo    | category_import  | foo_category_import   | Category import for foo      | import |
    Given I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the imports page
    Then I should see the filters Code, Label, Job, Connector and Status
    And the grid should contain 3 elements
    And I should see import profiles acme_product_import, acme_category_import and foo_category_import
    And I should not see import profile acme_product_export

  Scenario: Successfully filter by code
    Given I am on the imports page
    When I filter by "Code" with value "acme"
    Then the grid should contain 2 elements
    And I should see import profiles acme_product_import and acme_category_import
    And I should not see import profiles foo_product_import and acme_product_export 

  Scenario: Successfully filter by label
    Given I am on the imports page
    When I filter by "Label" with value "Product"
    Then the grid should contain 1 element
    And I should see import profiles acme_product_import
    And I should not see import profiles acme_product_export, acme_category_import and foo_category_import

  Scenario: Successfully filter by job
    Given I am on the imports page
    When I filter by "Job" with value "category_import"
    Then the grid should contain 2 elements
    And I should see import profiles acme_category_import and foo_category_import
    And I should not see import profiles acme_product_export and acme_product_import

  Scenario: Successfully filter by connector
    Given I am on the imports page
    When I filter by "Connector" with value "Akeneo"
    Then the grid should contain 3 elements
    And I should see import profiles acme_product_import, acme_category_import and foo_category_import
    And I should not see import profile acme_product_export

  Scenario: Successfully filter by status
    Given I am on the imports page
    When I filter by "Status" with value "Ready"
    Then the grid should contain 3 elements
    And I should see import profiles acme_product_import, acme_category_import and foo_category_import
    And I should not see import profile acme_product_export
