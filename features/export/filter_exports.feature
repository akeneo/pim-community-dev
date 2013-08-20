@javascript
Feature: Filter export profiles
  In order to filter export profiles in the catalog
  As a user
  I need to be able to filter export profiles in the catalog

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                       | type   |
      | Akeneo    | product_export   | acme_product_export   | Product export for Acme.com | export |
      | Akeneo    | attribute_export | acme_attribute_export | Attribute export            | export |
      | Akeneo    | product_export   | foo_product_export    | Product export for foo      | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com | import |
    Given I am logged in as "admin"

  Scenario: Successfully display filters
    Given I am on the exports page
    Then I should see the filters Code, Label, Job, Connector and Status
    And the grid should contain 3 elements
    And I should see export profiles acme_product_export, acme_attribute_export and foo_product_export
    And I should not see export profile acme_product_import

  Scenario: Successfully filter by code
    Given I am on the exports page
    When I filter by "Code" with value "acme"
    Then the grid should contain 2 elements
    And I should see export profiles acme_product_export and acme_attribute_export
    And I should not see export profiles acme_product_import and acme_product_export 

  Scenario: Successfully filter by label
    Given I am on the exports page
    When I filter by "Label" with value "Product export"
    Then the grid should contain 2 elements
    And I should see export profiles acme_product_export and foo_product_export
    And I should not see export profiles acme_product_import and acme_attribute_export

  Scenario: Successfully filter by job
    Given I am on the exports page
    When I filter by "Job" with value "product_export"
    Then the grid should contain 2 elements
    And I should see export profiles acme_product_export and foo_product_export
    And I should not see export profiles acme_product_import and acme_attribute_export

  Scenario: Successfully filter by connector
    Given I am on the exports page
    When I filter by "Connector" with value "Akeneo"
    Then the grid should contain 3 elements
    And I should see export profiles acme_product_export, acme_attribute_export and foo_product_export
    And I should not see export profile acme_product_import

  Scenario: Successfully filter by status
    Given I am on the exports page
    When I filter by "Status" with value "Ready"
    Then the grid should contain 3 elements
    And I should see export profiles acme_product_export, acme_attribute_export and foo_product_export
    And I should not see export profile acme_product_import
