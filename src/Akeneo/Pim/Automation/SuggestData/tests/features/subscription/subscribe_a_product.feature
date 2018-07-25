@acceptance-back
Feature: Subscribe a product to PIM.ai
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to PIM.ai

  Scenario: Successfully subscribe a product to PIM.ai
    Given the following attribute:
      | code  | type                      |
      | brand | pim_catalog_text          |
      | mpn   | pim_catalog_text          |
      | sku   | pim_catalog_identifier    |
      | asin  | pim_catalog_text          |
    And the following family:
      | code | attributes           |
      | tshirt | sku,mpn,brand,asin |
    And the following product:
      | identifier | family | mpn | brand | asin |
      | my_tshirt  | tshirt | 156 | Foo   | 7854 |


  #Scenario: Successfully subscribe a product to PIM.ai that does not exist on PIM.ai

  #Scenario: Fail to subscribe a product that is already subscribed to PIM.ai

  #Scenario: Fail to subscribe a product that does not exist

  #Scenario: Fail to subscribe a product if the identifier mapping is empty

  #Scenario: Fail to subscribe a product that does not have any value on mapped identifiers
