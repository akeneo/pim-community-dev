Feature: Validate the quantified associations of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to have validation errors for quantified associations

  Background:
    Given a catalog with the attribute "sku" as product identifier

  @acceptance-back
  Scenario: Cannot save a product with a nonexistent quantified association type
    Given a product with a quantified link where the association type does not exist
    Then there is the validation error "pim_catalog.constraint.quantified_associations.association_type_does_not_exist"

  @acceptance-back
  Scenario: Cannot save a product with a quantified link in a normal association type
    Given a product with a quantified link where the association type is not quantified
    Then there is the validation error "pim_catalog.constraint.quantified_associations.association_type_is_not_quantified"

  @acceptance-back
  Scenario: Cannot save a product with a quantified link in a normal association type
    Given a product with a quantified link where the association type is not quantified
    Then there is the validation error "pim_catalog.constraint.quantified_associations.association_type_is_not_quantified"

  # TODO:
  # target type is invalid
  # association quantity is an integer
  # association quantity is at least 1
  # association quantity is at max 9999
  # product does not exist
  # product models does not exist
  # maximum 100 associations
  # cannot use quantified association type in normal associations
