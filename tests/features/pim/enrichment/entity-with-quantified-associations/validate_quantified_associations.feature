Feature: Validate the quantified associations of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to have validation errors for quantified associations

  Background:
    Given a catalog with the attribute "sku" as product identifier

  @acceptance-back
  Scenario: Cannot save a product with a nonexistent quantified association type
    Given a product with a quantified link where the association type does not exist
    Then there is the validation error "This association type doesn't exist. Please, check if it has been deleted before trying again."

  @acceptance-back
  Scenario: Cannot save a product with a quantified link in a normal association type
    Given a product with a quantified link where the association type is not quantified
    Then there is the validation error "This association type isn't used to write quantities. Please, choose a different one before trying again."

  @acceptance-back
  Scenario Outline: Cannot save a product with a quantified link with an invalid quantity
    Given a product without quantified associations
    When I associate a product to this product with the quantity "<quantity>"
    Then there is the validation error ""<quantity>" is an invalid quantity. Please, write a value between 1 and 9999."
    Examples:
      | quantity |
      | -1       |
      | 0        |
      | 10000    |

  @acceptance-back
  Scenario Outline: Can save a product with a quantified link with a valid quantity
    Given a product without quantified associations
    When I associate a product to this product with the quantity "<quantity>"
    Then the product is valid
    Examples:
      | quantity |
      | 1        |
      | 9999     |

  @acceptance-back
  Scenario: Cannot save a product with a quantified link with nonexistent product
    Given a product without quantified associations
    When I associate a nonexistent product to this product with a quantity
    Then there is the validation error "The following products don't exist: accessory. Please, check if they have been deleted before trying again."

  @acceptance-back
  Scenario: Cannot save a product with a quantified link with nonexistent product model
    Given a product without quantified associations
    When I associate a nonexistent product model to this product with a quantity
    Then there is the validation error "The following product models don't exist: accessory. Please, check if they have been deleted before trying again."

  @acceptance-back
  Scenario Outline: Cannot save a product with more than 100 quantified associations
    Given a product without quantified associations
    When I associate "<nb_products>" products and "<nb_product_models>" product models with a quantity to this product
    Then there is the validation error "You've reached the limit of 100 associations. Please delete some existing associations before adding new ones."
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
    Given a product with invalid quantified link type
    Then there is the validation error "The link type "product_drafts" doesn't exist. You can only associate products or product models."

  @acceptance-back
  Scenario: Cannot save a product model with an invalid quantified association
    Given a product model with an invalid quantified association
    Then there is at least a validation error on this product model
