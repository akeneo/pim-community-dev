@javascript
Feature: Sort export profiles
  In order to sort export profiles in the catalog
  As a user
  I need to be able to sort export profiles by several columns in the catalog

  Background:
    Given the following jobs:
      | connector | alias            | code           | label                        | type   |
      | Akeneo    | product_export   | acme_product   | Product Acme      | export |
      | Akeneo    | attribute_export | acme_attribute | Attribute         | export |
      | Akeneo    | product_export   | foo_product    | Product           | export |
      | Akeneo    | category_export  | acme_category  | Category for Acme | export |
    Given I am logged in as "admin"

  Scenario: Successfully display the sortable columns
    Given I am on the exports page
    Then the datas can be sorted by Code, Label, Job, Connector and Status
    And the datas are sorted ascending by code
    And I should see sorted locales acme_attribute, acme_category, acme_product and foo_product

  Scenario: Successfully sort export profiles by code ascending
    Given I am on the exports page
    When I sort by "code" value ascending
    Then I should see sorted export profiles acme_attribute, acme_category, acme_product and foo_product

  Scenario: Successfully sort export profiles by code descending
    Given I am on the exports page
    When I sort by "code" value descending
    Then I should see sorted export profiles foo_product, acme_product, acme_category and acme_attribute

  Scenario: Successfully sort export profiles by label ascending
    Given I am on the exports page
    When I sort by "label" value ascending
    Then I should see sorted export profiles acme_attribute, acme_category, foo_product and acme_product

  Scenario: Successfully sort export profiles by label descending
    Given I am on the exports page
    When I sort by "label" value descending
    Then I should see sorted export profiles acme_product, foo_product, acme_category and acme_attribute

  Scenario: Successfully sort export profiles by job ascending
    Given I am on the exports page
    When I sort by "job" value ascending
    Then I should see sorted export profiles acme_attribute, acme_category, acme_product, foo_product

  Scenario: Successfully sort export profiles by job descending
    Given I am on the exports page
    When I sort by "job" value descending
    Then I should see sorted export profiles acme_product, foo_product, acme_category and acme_attribute

  Scenario: Successfully sort export profiles by connector ascending
    Given I am on the exports page
    When I sort by "connector" value ascending
    Then I should see sorted export profiles acme_product, acme_attribute, foo_product and acme_category

  Scenario: Successfully sort export profiles by connector descending
    Given I am on the exports page
    When I sort by "connector" value descending
    Then I should see sorted export profiles acme_product, acme_attribute, foo_product and acme_category

  Scenario: Successfully sort export profiles by status ascending
    Given I am on the exports page
    When I sort by "status" value ascending
    Then I should see sorted export profiles acme_product, acme_attribute, foo_product and acme_category

  Scenario: Successfully sort export profiles by status descending
    Given I am on the exports page
    When I sort by "status" value descending
    Then I should see sorted export profiles acme_product, acme_attribute, foo_product and acme_category
