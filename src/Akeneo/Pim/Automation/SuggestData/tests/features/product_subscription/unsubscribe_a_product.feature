@acceptance-back
Feature: Unsubscribe a product to Franklin
  In order to manage the products I subscribed to
  As Julia
  I want to unsubscribe a product to Franklin

  @end-to-end @javascript
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
