@javascript
Feature: Sort import profiles
  In order to sort import profiles in the catalog
  As a user
  I need to be able to sort import profiles by several columns in the catalog

  Background:
    Given the following jobs:
      | connector            | alias            | code           | label             | type   |
      | Akeneo CSV Connector | product_import   | acme_product   | Product Acme      | import |
      | Akeneo CSV Connector | attribute_import | acme_attribute | Attribute         | import |
      | Akeneo CSV Connector | product_import   | foo_product    | Product           | import |
      | Akeneo CSV Connector | category_import  | acme_category  | Category for Acme | import |
    Given I am logged in as "admin"

  Scenario: Successfully sort imports
    Given I am on the imports page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label, job, connector and status
