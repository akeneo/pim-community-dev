Feature: Validate identifier values of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for identifier attributes

  Background:
    Given an authenticated user
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"
    And the following attributes:
      | code        | type                   | scopable | localizable |
      | sku         | pim_catalog_identifier | 0        | 0           |
      | ean         | pim_catalog_identifier | 0        | 0           |
      | isbn        | pim_catalog_identifier | 0        | 0           |
    And a product with the following values:
      | attribute | data            |
      | sku       | game_of_thrones |
      | ean       | 0123456789      |
      | isbn      | 2-7654-1005-4   |

  @acceptance-back
  Scenario: Providing valid values for the identifier attributes should not raise any error
    When a product is created with values:
      | attribute | data          |
      | sku       | new_product   |
      | ean       | 0132675789065 |
      | isbn      | 2-7644-1002-3 |
    Then no error is raised

  @acceptance-back
  Scenario: Creating a product with a duplicate value for the main identifier should raise an error
    When a product is created with values:
      | attribute | data            |
      | sku       | game_of_thrones |
    Then the error 'The game_of_thrones identifier is already used for another product.' is raised

  @acceptance-back
  Scenario: Creating a product with an invalid value for the main identifier should raise an error
    When a product is created with values:
      | attribute | json_data                             |
      | sku       | "some,sku;withinvalid, characters   " |
    Then the error 'This field should not contain any comma or semicolon or leading/trailing space' is raised

  @acceptance-back
  Scenario: Creating a product with a duplicate value for a non-main identifier should raise an error
    When a product is created with values:
      | attribute | data             |
      | sku       | duplicate_values |
      | ean       | 0123456789       |
      | isbn      | 3-1234-2003-7    |
    Then the error 'The 0123456789 value is already set on another product for the ean identifier attribute.' is raised

  @acceptance-back
  Scenario: Creating a product with an invalid value for a non-main identifier should raise an error
    When a product is created with values:
      | attribute | json_data      |
      | isbn      | "  test;\toto" |
    Then the error 'This field should not contain any comma or semicolon or leading/trailing space' is raised
