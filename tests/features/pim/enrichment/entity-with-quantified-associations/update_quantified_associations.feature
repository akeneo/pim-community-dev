Feature: Update the quantified associations of an entity
  In order to associate products and product models with quantities
  As a regular user
  I need to be able to update the quantified associations

  Background:
    Given a catalog with the attribute "sku" as product identifier
    And an authentified administrator
    And a quantified association type "PACK"

  @acceptance-back
  Scenario: Associate a product to another product with quantity
    Given a product without quantified associations
    When I associate a product to this product with a quantity
    Then this product should be associated to this other product

  @acceptance-back
  Scenario: Associate a product to another product model with quantity
    Given a product model without quantified associations
    When I associate a product to this product model with a quantity
    Then this product model should be associated to this other product

  @acceptance-back
  Scenario: Associate a product model to another product model with quantity
    Given a product model without quantified associations
    When I associate a product model to this product model with a quantity
    Then this product model should be associated to this other product model

  @acceptance-back
  Scenario: Change the quantity in a quantified association defined in the parent of a product variant
    Given a product variant without quantified associations
    And this product has a parent with a quantified associations
    When I add the same quantified association with a different quantity
    Then this product should have this quantified association and all the other parent quantified associations
