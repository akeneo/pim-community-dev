@acceptance-back
Feature: Unsubscribe a product to Franklin
  In order to manage the products I subscribed to
  As Julia
  I want to unsubscribe a product to Franklin

  @end-to-end @javascript @critical
  Scenario: Successfully unsubscribe a product from Franklin
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should not be subscribed

  Scenario: Successfully unsubscribe a deleted product from Franklin
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I delete the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should not be subscribed

  Scenario: Failed to unsubscribe a product with an invalid token
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    And Franklin is configured with an expired token
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should be subscribed
    #And a token invalid message for subscription should be sent

#  Scenario: Failed to unsubscribe a product when Franklin server is down
#    Given the product "B00EYZY6AC" of the family "router"
#    And the product "B00EYZY6AC" is subscribed to Franklin
#    And Franklin server is down
#    When I unsubscribe the product "B00EYZY6AC"
#    Then the product "B00EYZY6AC" should be subscribed

  Scenario: Failed to unsubscribe a product that is not subscribed
    Given the product "B00EYZY6AC" of the family "router"
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should not be subscribed
    And a product not subscribed message should be sent
