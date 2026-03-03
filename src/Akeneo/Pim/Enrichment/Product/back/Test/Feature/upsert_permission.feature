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
    And the following locales "en_US,fr_FR"
    And the following "ecommerce" channel with locales "en_US,fr_FR"
    And the ROLE_ADMIN,ROLE_USER roles
    And the All,Manager,Redactor user groups
    And the julia Manager user
    And the marie Redactor user
    And the master category
    And the print category

  Scenario: Can create a product without category
    Given a set text value intent on the "a_text" attribute with the "test" text value
    When the "marie" user upserts the "foo" product
    Then there is no violation

  Scenario: Update a product when user group is owner
    Given the Manager user group is owner on the master category
    And a product with foo identifier in the master category
    And a set text value intent on the "a_text" attribute with the "test" text value
    When the "julia" user upserts the "foo" product
    Then there is no violation

  Scenario: Update a product when user group is not owner
    Given the Manager user group is owner on the master category
    And a product with foo identifier in the print category
    And a set text value intent on the "a_text" attribute with the "test" text value
    When the "julia" user upserts the "foo" product
    Then there is a violation with message: You don't have access to products in any tree, please contact your administrator

  Scenario: Create a product when one user's group is editable on locale
    Given the Manager user group is owner on the master category
    And the Manager user group has editable permission on the en_US locale
    And a product with foo identifier in the master category
    And a set text value intent on the "localizable_scopable_text" attribute, the "ecommerce" channel and the "en_US" locale with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is no violation

  Scenario: Create a product when one user's group is not editable on locale
    Given the Manager user group is owner on the master category
    And the Manager user group has editable permission on the en_US locale
    And a product with foo identifier in the master category
    And a set text value intent on the "localizable_scopable_text" attribute, the "ecommerce" channel and the "fr_FR" locale with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is a violation with message: You don't have access to product data in any activated locale, please contact your administrator
