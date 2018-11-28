@acceptance-back
Feature: Subscribe a product to Franklin
  In order to automatically enrich my products
  As Julia
  I want to subscribe a product to Franklin

  @end-to-end @javascript
  Scenario: Successfully subscribe a product to Franklin
    Given a system administrator configures Franklin using a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
      | upc           | pim_upc        |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should be subscribed
    And there should be a proposal for product B00EYZY6AC

  Scenario: Fail to subscribe a product without family
    Given Franklin is configured with a valid token
    And the product without family "product_without_family"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I subscribe the product "product_without_family" to Franklin
    Then the product "product_without_family" should not be subscribed
    And an invalid family message should be sent

  Scenario: Fail to subscribe a product that does not have any values on mapped identifiers
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an invalid values message should be sent

  Scenario: Fail to subscribe a product that is already subscribed to Franklin
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should be subscribed
    And an already subscribed message should be sent

  Scenario: Fail to subscribe a product with an invalid token
    Given the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And Franklin is configured with an expired token
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And a token invalid message for subscription should be sent

  Scenario: Subscribe a product without enough money on Franklin account
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And there are no more credits on my Franklin account
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And a not enough credit message should be sent

#  Scenario: Fail to subscribe a product that does not exist
#    Given Franklin is configured with a valid token
#    And the product "fake" does not exist
#    When I subscribe the product "fake" to Franklin
#    Then the product "fake" should not be subscribed

  Scenario: Fail to subscribe a product with an invalid upc
    Given Franklin is configured with a valid token
    And the product "invalidupc" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I subscribe the product "invalidupc" to Franklin
    Then the product "invalidupc" should not be subscribed
    And a Franklin subscription error message should be sent

  Scenario: Fail to subscribe a product that does not have MPN and Brand filled together
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | mpn           | mpn            |
      | brand         | pim_brand      |
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an invalid MPN and Brand message should be sent

  #Scenario: Handle a bad request to Franklin

  Scenario: Dealing with error on product subscription when Franklin server is down
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And Franklin server is down
    When I subscribe the product "B00EYZY6AC" to Franklin
    Then the product "B00EYZY6AC" should not be subscribed
    And an invalid subscription message should be sent
