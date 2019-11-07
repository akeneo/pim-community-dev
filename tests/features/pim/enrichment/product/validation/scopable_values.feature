Feature: Validate scopable values of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for scopable values

  Background:
    Given the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"
    And the following attributes:
      | code        | type                     | scopable |
      | sku         | pim_catalog_identifier   | 0        |
      | description | pim_catalog_text         | 1        |

  @acceptance-back
  Scenario: Providing an existing scopable attribute should not raise an error
    When a product is created with values:
      | attribute   | data    | scope     |
      | description | my desc | ecommerce |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a non existing channel should not raise an error
    When a product is created with values:
      | attribute   | data    | scope   |
      | description | my desc | unknown |
    Then the error 'Attribute "description" expects an existing scope, "unknown" given.' is raised
