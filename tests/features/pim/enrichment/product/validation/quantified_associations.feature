Feature: Validate the quantified associations of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to have validation errors for quantified associations

  Background:
    Given a catalog with the attribute "sku" as product identifier

#  @acceptance-back
#  Scenario: Cannot save a product with an invalid quantified association
#    Given a product with an invalid quantified association
#    When I try to save this product
#    Then there is a validation error on this quantified association

#  @acceptance-back
#  Scenario: Cannot save a product model with an invalid quantified association
#    Given a product model with an invalid quantified association
#    When I try to save this product model
#    Then there is the validation error on this quantified association

#  @acceptance-back
#  Scenario: Quantified association has validation error when the association type does not exist
#    Given quantified associations with:
#    """
#    {
#      "INVALID_ASSOCIATION_TYPE": {
#        "products": [],
#        "product_models": []
#      }
#    }
#    """
#    When I validate this quantified associations
#    Then there is the validation error "test"

  # TODO:
  # association type does not exist
  # target type is invalid
  # association quantity is an integer
  # association quantity is at least 1
  # association quantity is at max 9999
  # product does not exist
  # product models does not exist
  # maximum 100 associations
