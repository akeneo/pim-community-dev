@acceptance-back
Feature: Subscribe a product to PIM.ai
  In order to automatically enrich products
  As a marketing manager
  I need to subscribe a product to PIM.ai

  Scenario: Successfully subscribe a product to PIM.ai
    Given a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |
    And a product with an identifier "a-super-product"
    When I subscribe the product "a-super-product" to PIM.ai
    Then the product "a-super-product" should be subscribed

#  Scenario: Successfully subscribe a product to PIM.ai that does not exist on PIM.ai
#
#  Scenario: Fail to subscribe a product that is already subscribed to PIM.ai ??
#
#  Scenario: Fail to subscribe a product that does not exist
#
#  Scenario: Fail to subscribe a product if the identifier mapping is empty
#
#  Scenario: Fail to subscribe a product that does not have any value on mapped identifiers
