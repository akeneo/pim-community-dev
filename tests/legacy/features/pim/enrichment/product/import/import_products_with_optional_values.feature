Feature: Import product information with optional values
  In order to enrich product without family or with attributes not belonging to a family
  As a product manager
  I need to be able to import optional product values (add them when data provided, remove them when no data provided)

  Scenario: Successfully add an optional product value
    Given a "footwear" catalog configuration
    And the following attribute:
      | code           | type             | localizable | scopable | group |
      | opt_att_global | pim_catalog_text | 0           | 0        | other |
      | opt_att_local  | pim_catalog_text | 1           | 0        | other |
      | opt_att_scope  | pim_catalog_text | 0           | 1        | other |
    And the following product:
      | sku              | family | opt_att_global |
      | caterpillar-pim  | boots  |                |
      | caterpillar-pam  |        |                |
      | caterpillar-poum |        | Poum           |
      | caterpillar-pum  |        | Pum            |
    And the following CSV file to import:
      """
      sku;opt_att_global;opt_att_local-en_US;opt_att_scope-tablet
      caterpillar-pim;"Pim";"PimUS";"PimTablet"
      caterpillar-pam;"Pam";;"PamTablet"
      caterpillar-poum;;;
      caterpillar-pum;PimPamPoum;;
      """
    When the products are imported via the job csv_footwear_product_import
    Then there should be 4 products
    And attribute opt_att_global of "caterpillar-pam" should be "Pam"
    And the tablet scopable value opt_att_scope of "caterpillar-pam" should be "PamTablet"
    And attribute opt_att_global of "caterpillar-pum" should be "PimPamPoum"
    And the product "caterpillar-poum" should not have the following values:
      | opt_att_global       |
      | opt_att_local-en_US  |
      | opt_att_scope-tablet |
    And the product "caterpillar-pim" should not have the following values:
      | opt_att_global |

