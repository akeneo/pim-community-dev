Feature: Validate the quantified associations of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to have validation errors for quantified associations

  Background:
    Given a catalog with the attribute "sku" as product identifier
    And an authentified administrator

  @acceptance-back
  Scenario: Cannot save a product with a nonexistent quantified association type
    When a product is associated with a quantity for an association type that does not exist
    Then this product has a validation error about association type does not exist

  @acceptance-back
  Scenario: Cannot save a product with a quantified link in a normal association type
    When a product is associated with a quantity for an association type that is not quantified
    Then this product has a validation error about association type is not quantified

  @acceptance-back
  Scenario Outline: Cannot save a product with a quantified link with an invalid quantity
    Given a product without quantified associations
    When I associate a product to this product with the quantity "<quantity>"
    Then this product has a validation error about invalid quantity
    Examples:
      | quantity   |
      | -1         |
      | 0          |
      | 2147483648 |

  @acceptance-back
  Scenario Outline: Can save a product with a quantified link with a valid quantity
    Given a product without quantified associations
    When I associate a product to this product with the quantity "<quantity>"
    Then the product is valid
    Examples:
      | quantity   |
      | 1          |
      | 2147483647 |

  @acceptance-back
  Scenario: Cannot save a product with a quantified link with nonexistent product
    Given a product without quantified associations
    When I associate a nonexistent product to this product with a quantity
    Then this product has a validation error about product do not exist

  @acceptance-back
  Scenario: Cannot save a product with a quantified link with nonexistent product uuid
    Given a product without quantified associations
    When I associate a nonexistent product uuid to this product with a quantity
    Then this product has a validation error about product do not exist

  @acceptance-back
  Scenario: Cannot save a product with a quantified link with nonexistent product model
    Given a product without quantified associations
    When I associate a nonexistent product model to this product with a quantity
    Then this product has a validation about product models do not exist

  @acceptance-back
  Scenario Outline: Cannot save a product with more than 100 quantified associations
    Given a product without quantified associations
    When I associate "<nb_products>" products and "<nb_product_models>" product models with a quantity to this product
    Then this product has a validation about maximum number of associations
    Examples:
      | nb_products | nb_product_models |
      | 101         | 0                 |
      | 0           | 101               |
      | 50          | 51                |
      | 51          | 50                |

  @acceptance-back
  Scenario Outline: Can save a product with 100 quantified associations
    Given a product without quantified associations
    When I associate "<nb_products>" products and "<nb_product_models>" product models with a quantity to this product
    Then the product is valid
    Examples:
      | nb_products | nb_product_models |
      | 100         | 0                 |
      | 0           | 100               |
      | 50          | 50                |

  @acceptance-back
  Scenario: Cannot save a product with invalid quantified link type
    When a product is associated with invalid quantified link type
    Then this product has a validation error about unexpected link type

  @acceptance-back
  Scenario: Cannot save a product model with an invalid quantified association
    When a product model is associated with an invalid quantified association
    Then there is at least a validation error on this product model

  @acceptance-back
  Scenario: Cannot save a product with a quantified association type used in a normal association
    Given a product without associations
    When I add an association without quantity to this product using a quantified association type
    Then this product has a validation error about association type should not be quantified

  @acceptance-back
  Scenario: Cannot save a product model with a quantified association type used in a normal association
    Given a product model without associations
    When I add an association without quantity to this product model using a quantified association type
    Then this product model has a validation error about association type should not be quantified
