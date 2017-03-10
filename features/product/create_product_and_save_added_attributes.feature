@javascript
Feature: Create product and save a new product value
  In order to enrich a new product
  As a product manager
  I need to be able to create a product, add attributes and save

  @jira https://akeneo.atlassian.net/browse/PIM-5666
  Scenario: Successfully create a product, fill in product values with 0 and save
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                         | group     | decimals_allowed |
      | tmp_price | Tmp Price   | pim_catalog_price_collection | marketing | 0                |
    And I am logged in as "Julia"
    And I am on the products page
    And I create a new product
    And I fill in the following information in the popin:
      | SKU             | gladiator |
      | Choose a family | Sandals   |
    And I press the "Save" button in the popin
    And I should be on the product "gladiator" edit page
    And I am on the "gladiator" product page
    And I add available attributes Rate of sale, Weight and Tmp Price
    And I visit the "Product information" group
    And I change the Weight to "0"
    And I visit the "Marketing" group
    And I change the "Rate of sale" to "0"
    And I fill in the following information:
      | Tmp Price | 0 EUR |
    And I save the product
    Then the product "gladiator" should have the following values:
      | rate_sale | 0           |
      | tmp_price | 0.00 EUR    |
      | weight    | 0.0000 GRAM |
