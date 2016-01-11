@javascript
Feature: Import product information with optional values
  In order to enrich product without family or with attributes not belonging to a family
  As a product manager
  I need to be able to import optional product values (add them when data provided, remove them when no data provided)

  Scenario: Successfully add an optional product value
    Given a "footwear" catalog configuration
    And the following attribute:
      | code           | type | localizable | scopable |
      | opt_att_global | text | no          | no       |
      | opt_att_local  | text | yes         | no       |
      | opt_att_scope  | text | no          | yes      |
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
    And the following job "footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    When I am on the "footwear_product_import" import job page
    And I launch the import job
    And I wait for the "footwear_product_import" job to finish
    Then there should be 4 products
    And the english opt_att_global of "caterpillar-pim" should be "Pim"
    And the english opt_att_local of "caterpillar-pim" should be "PimUS"
    And the english tablet opt_att_scope of "caterpillar-pim" should be "PimTablet"
    And the english opt_att_global of "caterpillar-pam" should be "Pam"
    And the english tablet opt_att_scope of "caterpillar-pam" should be "PamTablet"
    And the english opt_att_global of "caterpillar-poum" should be ""
    And the english opt_att_global of "caterpillar-pum" should be "PimPamPoum"

