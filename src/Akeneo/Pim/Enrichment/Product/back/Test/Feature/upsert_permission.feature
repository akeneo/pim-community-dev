@acceptance-back
Feature: Upsert a product
  In order to enrich my catalog of products
  As a product manager
  I need to be able to upsert a product following my permissions

  Background:
    Given an authenticated user
    And the following attributes:
      | code                      | type                   | localizable | scopable |
      | sku                       | pim_catalog_identifier |           0 |        0 |
      | a_text                    | pim_catalog_text       |           0 |        0 |
      | localizable_scopable_text | pim_catalog_text       |           1 |        1 |
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"
    And the ROLE_ADMIN,ROLE_USER roles
    And the All,Manager,Redactor user groups
    And the julia Manager user
    And the marie Redactor user
    And the master category
    And the Manager user group is owner on the master category

  Scenario: Julia can update a product
    Given a product with foo identifier in the master category
    And a set text value intent on the "a_text-null-null" attribute with the "test" text value
    When the "julia" user upserts a product with the "foo" identifier
    Then there is no violation

  Scenario: Marie can create a product without category
    Given a set text value intent on the "a_text-null-null" attribute with the "test" text value
    When the "marie" user upserts a product with the "foo" identifier
    Then there is no violation

  Scenario: Marie cannot update a product when she is not owner on the category (EE)
    Given a product with foo identifier in the master category
    And a set text value intent on the "a_text-null-null" attribute with the "test" text value
    When the "marie" user upserts a product with the "foo" identifier
    Then there is a violation with message: You don't have access to products in any tree, please contact your administrator
