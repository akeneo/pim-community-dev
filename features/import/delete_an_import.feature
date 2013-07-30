@javascript
Feature: Delete import
  In order to delete an import job that have been created
  As an user
  I need to be able to view a list of them
  And I need to delete one of them or cancel my operation

  Background:
    Given the following jobs:
      | connector | alias            | code                  | label                       | type   |
      | Akeneo    | product_export   | acme_product_export    | Product export for foo      | export |
      | Akeneo    | product_import   | acme_product_import   | Product import for Acme.com | import |
      | Akeneo    | attribute_import | acme_attribute_import | Attribute import            | import |
    Given I am logged in as "admin"

  Scenario: Successfully delete an import job
    Given I am on the imports index page
    And the grid should contain 2 elements
    When I delete the "acme_product_import" job
    Then I confirm on the grid modal window
    Then the grid should contain 1 element
    And the grid should contains the elements "acme_attribute_import"
    And the grid should not contains the elements "acme_product_export", "acme_product_import"

  Scenario: Successfully cancel the deletion of an import job
    Given I am on the imports index page
    And the grid should contain 2 elements
    When I delete the "acme_product_import" job
    Then I cancel on the grid modal window
    Then the grid should contain 2 elements
    And the grid should contains the elements "acme_product_import", "acme_product_import"
    And the grid should not contains the elements "acme_product_export"
