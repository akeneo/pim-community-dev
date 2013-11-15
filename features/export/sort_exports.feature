@javascript
Feature: Sort export profiles
  In order to sort export profiles in the catalog
  As a user
  I need to be able to sort export profiles by several columns in the catalog

  Scenario: Successfully sort exports
    Given the "default" catalog configuration
    And the following jobs:
      | connector            | alias            | code           | label             | type   |
      | Akeneo CSV Connector | product_export   | acme_product   | Product Acme      | export |
      | Akeneo CSV Connector | attribute_export | acme_attribute | Attribute         | export |
      | Akeneo CSV Connector | product_export   | foo_product    | Product           | export |
      | Akeneo CSV Connector | category_export  | acme_category  | Category for Acme | export |
    And I am logged in as "admin"
    And I am on the exports page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
