Feature: Execute add and set associations rules
  In order to ease the enrichment of the catalog
  As a manager
  I can manage my products associations with rules

  Background:
    Given a "clothing" catalog configuration
    And the following family variants:
      | code             | family  | label-en_US      | variant-axes_1 | variant-attributes_1 |
      | jackets_by_color | jackets | Jackets by color | main_color     | main_color,sku       |
    And the root product model model_1 with family variant jackets_by_color
    And the root product model model_2 with family variant jackets_by_color
    And the following products:
      | sku          | family  |
      | other-jacket | jackets |
      | super-jacket | jackets |
      | my-jacket    | jackets |
    And the following associations for product "my-jacket":
      | association_type | products     | product_models | groups          |
      | X_SELL           | super-jacket | model_1        | similar_jackets |
    And the following associations for product "super-jacket":
      | association_type | products     | product_models | groups |
      | X_SELL           | other-jacket | model_2        |        |


  @integration-back
  Scenario: replace associated products, product models and groups via a rule
    Given the following product rule definitions:
      """
      set_associations:
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  set
            field: associations
            value:
              X_SELL:
                products:
                  - other-jacket
                  - super-jacket
                product_models:
                  - model_2
                groups: []
      """
    When the product rule "set_associations" is executed
    Then the "my-jacket" product should have the following associations:
      | association_type | products                   | product_models | groups |
      | X_SELL           | other-jacket, super-jacket | model_2        |        |

  @integration-back
  Scenario: only replace associated products via a rule
    Given the following product rule definitions:
      """
      set_associated_products:
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  set
            field: associations
            value:
              X_SELL:
                products:
                  - other-jacket
      """
    When the product rule "set_associated_products" is executed
    Then the "my-jacket" product should have the following associations:
      | association_type | products     | product_models | groups          |
      | X_SELL           | other-jacket | model_1        | similar_jackets |

  @integration-back
  Scenario: only replace associated product models via a rule
    Given the following product rule definitions:
      """
      set_associated_product_models:
        conditions:
          - field:    sku
            operator: =
            value:    my-jacket
        actions:
          - type:  set
            field: associations
            value:
              X_SELL:
                product_models:
                  - model_1
                  - model_2
      """
    When the product rule "set_associated_product_models" is executed
    Then the "my-jacket" product should have the following associations:
      | association_type | products     | product_models  | groups          |
      | X_SELL           | super-jacket | model_1,model_2 | similar_jackets |

  @integration-back
  Scenario: only replace associated groups via a rule
    Given the following product rule definitions:
      """
      set_associated_groups:
        conditions:
          - field:    sku
            operator: =
            value: super-jacket
        actions:
          - type:  set
            field: associations
            value:
              X_SELL:
                groups:
                  - similar_jackets
      """
    When the product rule "set_associated_groups" is executed
    Then the "super-jacket" product should have the following associations:
      | association_type | products     | product_models | groups          |
      | X_SELL           | other-jacket | model_2        | similar_jackets |

  @integration-back
  Scenario: add associations via a rule
    Given the following product rule definitions:
      """
      add_associations:
        conditions:
          - field:    sku
            operator: =
            value: super-jacket
        actions:
          - type:  add
            field: associations
            items:
              X_SELL:
                products:
                  - my-jacket
                product_models:
                  - model_1
                groups:
                  - similar_jackets
      """
    When the product rule "add_associations" is executed
    Then the "super-jacket" product should have the following associations:
      | association_type | products                | product_models   | groups          |
      | X_SELL           | other-jacket, my-jacket | model_2, model_1 | similar_jackets |

  @integration-back
  Scenario: add associated products via a rule
    Given the following product rule definitions:
      """
      add_associated_products:
        conditions:
          - field:    sku
            operator: =
            value: my-jacket
        actions:
          - type:  add
            field: associations
            items:
              X_SELL:
                products:
                  - other-jacket
      """
    When the product rule "add_associated_products" is executed
    Then the "my-jacket" product should have the following associations:
      | association_type | products                   | product_models | groups          |
      | X_SELL           | super-jacket, other-jacket | model_1        | similar_jackets |

  @integration-back
  Scenario: add associated product models via a rule
    Given the following product rule definitions:
      """
      add_associated_product_models:
        conditions:
          - field:    sku
            operator: =
            value: other-jacket
        actions:
          - type:  add
            field: associations
            items:
              X_SELL:
                product_models:
                  - model_1
                  - model_2
      """
    When the product rule "add_associated_product_models" is executed
    Then the "other-jacket" product should have the following associations:
      | association_type | products | product_models   | groups          |
      | X_SELL           |          | model_1, model_2 |                 |

  @integration-back
  Scenario: add associated groups via a rule
    Given the following product rule definitions:
      """
      add_associated_groups:
        conditions:
          - field:    sku
            operator: =
            value: super-jacket
        actions:
          - type:  add
            field: associations
            items:
              X_SELL:
                groups:
                  - similar_jackets
      """
    When the product rule "add_associated_groups" is executed
    Then the "super-jacket" product should have the following associations:
      | association_type | products     | product_models | groups          |
      | X_SELL           | other-jacket | model_2        | similar_jackets |
