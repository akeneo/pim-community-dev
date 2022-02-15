@acceptance-back
Feature: Upsert a product
  In order to enrich my catalog of products
  As a product manager
  I need to be able to upsert a product

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
    And the julia manager user

  Scenario: Create an empty product with julia
    When the "julia" user upserts a product with the "foo" identifier
    Then there is no violation

  Scenario: Create an empty product with unknown user
    When the "-10" user id upserts a product with the "foo" identifier
    Then there is a violation with message: The "-10" user does not exist

  Scenario: Create an empty product with empty string identifier
    When the "julia" user upserts a product with the "" identifier
    Then there is a violation with message: The product identifier requires a non empty string

  Scenario: Create a product with text attribute value
    Given a set text value intent on the "a_text-null-null" attribute with the "test" text value
    When the "julia" user upserts a product with the "foo" identifier and the previous intents
    Then there is no violation

  Scenario: Create a product with unexpected locale and scope for text attribute value
    Given a set text value intent on the "a_text-ecommerce-en_US" attribute with the "test" text value
    When the "julia" user upserts a product with the "foo" identifier and the previous intents
    Then there is a violation with message: The a_text attribute does not require a locale, "en_US" was detected
    And there is a violation with message: The a_text attribute does not require a channel, "ecommerce" was detected

  Scenario: Cannot create a product with missing locale and scope for text attribute value
    Given a set text value intent on the "localizable_scopable_text-null-null" attribute with the "test" text value
    When the "julia" user upserts a product with the "foo" identifier and the previous intents
    Then there is a violation with message: The localizable_scopable_text attribute requires a channel
    And there is a violation with message: The localizable_scopable_text attribute requires a locale

  Scenario: Cannot create a product with unknown scope for text attribute value
    Given a set text value intent on the "localizable_scopable_text-unknown_scope-unknown_locale" attribute with the "test" text value
    When the "julia" user upserts a product with the "foo" identifier and the previous intents
    Then there is a violation with message: The unknown_scope channel does not exist

  Scenario: Cannot create a product with unknown locale for text attribute value
    Given a set text value intent on the "localizable_scopable_text-ecommerce-unknown_locale" attribute with the "test" text value
    When the "julia" user upserts a product with the "foo" identifier and the previous intents
    Then there is a violation with message: The unknown_locale locale is not activated for the ecommerce channel
