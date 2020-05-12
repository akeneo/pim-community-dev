Feature: Validate the quantified associations of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for quantified associations

#  Background:
#    Given the following locales "en_US"
#    And the following "ecommerce" channel with locales "en_US"
#    And the following attributes:
#      | code        | type                     | scopable |
#      | sku         | pim_catalog_identifier   | 0        |
#      | description | pim_catalog_text         | 1        |

#  @acceptance-back
#  Scenario: Providing an existing scopable attribute should not raise an error
#    When a product is created with values:
#      | attribute   | data    | scope     |
#      | description | my desc | ecommerce |
#    Then no error is raised

  @acceptance-back
  Scenario: Cannot save a product with an invalid quantified association
    Given a product with an invalid quantified association
    When I try to save this product
    Then there is the validation error on this quantified association

  @acceptance-back
  Scenario: Cannot save a product model with an invalid quantified association
    Given a product model with an invalid quantified association
    When I try to save this product model
    Then there is the validation error on this quantified association

  # association type does not exist
  # target type is invalid
  # association quantity is an integer
  # association quantity is at least 1
  # association quantity is at max 9999
  # product does not exist
  # product models does not exist
  # maximum 100 associations
