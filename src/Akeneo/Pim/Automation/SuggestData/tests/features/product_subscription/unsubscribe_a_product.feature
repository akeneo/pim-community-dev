@acceptance-back
Feature: Unsubscribe a product to Franklin
  In order to manage the products I subscribed to
  As Julia
  I want to unsubscribe a product to Franklin

  @end-to-end @javascript @critical
  Scenario: Successfully unsubscribe a product to Franklin
    Given a system administrator configures Franklin using a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should not be subscribed

  Scenario: Failed to unsubscribe a product with an invalid token
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    And Franklin is configured with an expired token
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should be subscribed

  Scenario: Failed to unsubscribe a product when Franklin server is down
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    And Franklin server is down
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should be subscribed

  Scenario: Failed to unsubscribe a product that is not subscribed
    Given the product "B00EYZY6AC" of the family "router"
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should be subscribed
