@acceptance-back
Feature: Subscribe a product to PIM.ai
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to PIM.ai

  Scenario: Successfully subscribe a product to PIM.ai
    Given the following attribute:
      | code  | type                   |
      | ean   | pim_catalog_text       |
      | sku   | pim_catalog_identifier |
    And the following family:
      | code | attributes |
      | tshirt | sku,ean  |
    And the following product:
      | identifier | family | ean          |
      | ts_0013    | tshirt | 606449099812 |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | ean            |
    When I subscribe the product "ts_0013" to PIM.ai
    Then the product "ts_0013" should be subscribed

  #Scenario: Successfully subscribe a product to PIM.ai that does not exist on PIM.ai
  # Tried with UPC 606449099813
  # Error 500 thrown by PIM.ai

  #Scenario: Fail to subscribe a product without family

  #Scenario: Fail to subscribe a product that does not exist

  #Scenario: Fail to subscribe a product with an invalid token
  # Should return a 403

  #Scenario: Fail to subscribe a product that has an incorrect UPC
  # wrong UPC format

  #Scenario: Fail to subscribe a product that is already subscribed to PIM.ai
  #  Given the following attribute:
  #    | code  | type                   |
  #    | ean   | pim_catalog_text       |
  #    | sku   | pim_catalog_identifier |
  #  And the following family:
  #    | code | attributes |
  #    | tshirt | sku,ean  |
  #  And the following product:
  #    | identifier | family | ean          |
  #    | ts_0013    | tshirt | 606449099812 |
  #  And a predefined mapping as follows:
  #    | pim_ai_code | attribute_code |
  #    | upc         | ean            |
  #  And I subscribe the product "ts_0013" to PIM.ai
  #  When I subscribe the product "ts_0013" to PIM.ai
  #  Then the product "ts_0013" should be subscribed

  #Scenario: Fail to subscribe a product that does not have any values on mapped identifiers

  #Scenario: Fail to subscribe a product that does not have one value on mapped identifiers
  # Check with MPN + Brand with Brand not filled

  #Scenario: Fail to subscribe a product with an invalid identifiers mapping
  # No request to PIM.ai

  #Scenario: Subscribe a product without enough money on PIM.ai account
  # Should return a 402

  #Scenario: Handle a bad request to PIM.ai
