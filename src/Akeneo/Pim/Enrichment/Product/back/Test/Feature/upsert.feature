@acceptance-back
Feature: Upsert a product
  In order to enrich my catalog of products
  As a product manager
  I need to be able to upsert a product

  Background:
    Given an authenticated user
    And the following attributes:
      | code        | type                    |
      | sku         | pim_catalog_identifier  |
      | a_text      | pim_catalog_text        |
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"
    And the ROLE_ADMIN,ROLE_USER roles
    And the All,Manager,Redactor user groups
    And the julia manager user

  Scenario: Create an empty product with julia
    When the "julia" user upserts a product with the "foo" identifier
    Then There is no violation

  Scenario: Create an empty product with unknown user
    When the "-10" user id upserts a product with the "foo" identifier
    Then There is a violation with message: The "-10" user does not exist

  Scenario: Create an empty product with unknown user
    When the "julia" user upserts a product with the "" identifier
    Then There is a violation with message: The product identifier requires a non empty string
