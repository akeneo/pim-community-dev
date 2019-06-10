@acceptance-back
Feature: Subscribe a product to Franklin
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to Franklin

  @end-to-end @javascript @critical
  Scenario: Successfully subscribe a product to Franklin
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
      | upc           | pim_upc        |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should be subscribed
    And there should be a proposal for product B00EYZY6AC

  Scenario: Successfully subscribe a product with brand and MPN to Franklin
    Given Franklin is configured with a valid token
    And the product "75024" of the family "camcorders"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
    When I subscribe the product "75024" to Franklin
    Then the product "75024" should be subscribed

  Scenario: Fail to subscribe a product without family
    Given Franklin is configured with a valid token
    And the product without family "product_without_family"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I subscribe the product "product_without_family" to Franklin
    Then the product "product_without_family" should not be subscribed
    And a family required message should be sent

  Scenario: Fail to subscribe a product that does not have any values on mapped identifiers
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an invalid values message should be sent

  Scenario: Fail to subscribe a product that is already subscribed to Franklin
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should be subscribed
    And an already subscribed message should be sent

  Scenario: Fail to subscribe a product with an invalid token
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And Franklin is configured with an expired token
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an authentication error message should be sent

  Scenario: Fail to subscribe a product when there is not enough money on Franklin account
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And there are no more credits on my Franklin account
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And a not enough credit message should be sent

  Scenario: Fail to subscribe a product with an invalid upc
    Given Franklin is configured with a valid token
    And the product "invalidupc" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I subscribe the product "invalidupc" to Franklin
    Then the product "invalidupc" should not be subscribed
    And an invalid values message should be sent

  Scenario: Fail to subscribe a product that does not have MPN and Brand filled together
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | mpn           | mpn            |
      | brand         | pim_brand      |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an invalid MPN and Brand message should be sent

  Scenario: Fail to subscribe a product when Franklin server is down
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And Franklin server is down
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And a data provider error message should be sent

  Scenario: Fail to subscribe a product variant to Franklin
    Given Franklin is configured with a valid token
    And the variant product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
      | upc           | pim_upc        |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an invalid variant message should be sent

  Scenario: Fail to subscribe a product that has an identical identifier to another subscribed product
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
      | upc           | pim_upc        |
    And the product "B00EYZY6AC" is subscribed to Franklin
    And the product "B00DWYW5ZD" that has the same ASIN that the product "B00EYZY6AC"
    When I subscribe the product "B00DWYW5ZD" to Franklin
    Then the product "B00DWYW5ZD" should not be subscribed
    And a subscription with same identifier already exist message should be sent
