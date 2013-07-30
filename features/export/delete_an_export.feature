@javascript
Feature: Delete export
  In order to delete an import job that have been created
  As a user
  I need to be able to view a list of them
  And I need to delete one of them or cancel my operation

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                       | type   |
      | Akeneo    | product_export   | acme_product_export   | Product export for Acme.com | export |
      | Akeneo    | attribute_export | acme_attribute_export | Attribute export            | export |
      | Akeneo    | product_export   | foo_product_export    | Product export for foo      | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com | import |
    Given I am logged in as "admin"

  Scenario: Successfully delete an export job
    Given I am on the exports index page
    Then the grid should contain 3 elements
    When I delete the "foo_product_export" job
    And I confirm on the grid modal window
    Then I should see "Item was deleted"
    And the grid should contain 2 elements
    And the grid should contain the elements "acme_product_export" and "acme_attribute_export"
    And the grid should not contain the elements "foo_product_export" and "acme_product_import"

  Scenario: Successfully cancel the deletion of an export job
    Given I am on the exports index page
    Then the grid should contain 3 elements
    When I delete the "foo_product_export" job
    And I cancel on the grid modal window
    Then the grid should contain 3 elements
    And the grid should contain the elements "acme_product_export", "acme_attribute_export" and "foo_product_export"
    And the grid should not contain the elements "acme_product_import"
