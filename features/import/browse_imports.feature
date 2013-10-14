@javascript
Feature: Browse imports
  In order to view the list of import jobs instance that have been created
  As a user
  I need to be able to view a list of them

  Background:
    Given the following jobs:
      | connector            | alias           | code                 | label                        | type   |
      | Akeneo CSV Connector | product_export  | acme_product_export  | Product export for Acme.com  | export |
      | Akeneo CSV Connector | product_import  | acme_product_import  | Product import for Acme.com  | import |
      | Akeneo CSV Connector | category_import | acme_category_import | Category import for Acme.com | import |
      | Akeneo CSV Connector | category_import | foo_category_import  | Category import for foo      | import |
    Given I am logged in as "admin"

  Scenario: Successfully display all the import jobs
    Given I am on the imports page
    Then the grid should contain 3 element
    And I should see import profiles acme_product_import, acme_category_import and foo_category_import
    And I should not see import profile acme_product_export
    And the row "acme_product_import" should contain:
      | column    | value                |
      | connector | Akeneo CSV Connector |

  Scenario: Successfully display columns
    Given I am on the imports page
    Then I should see the columns Code, Label, Job, Connector and Status
