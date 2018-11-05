@acceptance-back
Feature: Subscribe a product to Franklin
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to Franklin

  @end-to-end @javascript
  Scenario: Successfully subscribe a product to Franklin
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | asin        | asin           |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should be subscribed

  Scenario: Fail to subscribe a product without family
    Given the product without family "product_without_family"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | pim_upc        |
    When I subscribe the product "product_without_family" to Franklin
    Then the product "product_without_family" should not be subscribed

  Scenario: Fail to subscribe a product that does not have any values on mapped identifiers
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | pim_upc        |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed

  Scenario: Fail to subscribe a product that is already subscribed to Franklin
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | asin        | asin           |
    And Franklin is configured with a valid token
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should be subscribed

  Scenario: Fail to subscribe a product with an invalid token
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | asin        | asin           |
    And the Franklin token is expired
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed

  Scenario: Subscribe a product without enough money on Franklin account
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | asin        | asin           |
    And there are no more credits on my Franklin account
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed

  #Scenario: Fail to subscribe a product that does not exist

  #Scenario: Fail to subscribe a product that has an incorrect UPC
  # wrong UPC format

  Scenario: Fail to subscribe a product that does not have MPN and Brand filled together
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | mpn         | mpn            |
      | brand       | pim_brand      |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed

  #Scenario: Handle a bad request to Franklin
