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
    When the "julia" user upserts the "foo" product
    Then there is no violation

  Scenario: Create an empty product with unknown user
    When an unknown user tries to upsert the "foo" product
    Then there is a violation saying the user is unknown

  Scenario: Create a product with text attribute value
    Given a set text value intent on the "a_text" attribute with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is no violation

  Scenario: Create a product with unexpected locale and scope for text attribute value
    Given a set text value intent on the "a_text" attribute, the "ecommerce" channel and the "en_US" locale with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is a violation with message: The a_text attribute does not require a locale, "en_US" was detected
    And there is a violation with message: The a_text attribute does not require a channel, "ecommerce" was detected

  Scenario: Cannot create a product with missing locale and scope for text attribute value
    Given a set text value intent on the "localizable_scopable_text" attribute with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is a violation with message: The localizable_scopable_text attribute requires a channel
    And there is a violation with message: The localizable_scopable_text attribute requires a locale

  Scenario: Cannot create a product with unknown scope for text attribute value
    Given a set text value intent on the "localizable_scopable_text" attribute, the "unknown_scope" channel and the "unknown_locale" locale with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is a violation with message: The unknown_scope channel does not exist

  Scenario: Cannot create a product with unknown locale for text attribute value
    Given a set text value intent on the "localizable_scopable_text" attribute, the "ecommerce" channel and the "unknown_locale" locale with the "test" text value
    When the "julia" user upserts the "foo" product with the previous intent
    Then there is a violation with message: The unknown_locale locale is not activated for the ecommerce channel
