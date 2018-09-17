@acceptance-back
Feature: Unsubscribe a product to PIM.ai
  In order to manage the products I subscribed to
  As Julia
  I want to unsubscribe a product to PIM.ai

  Scenario: Successfully unsubscribe a product to PIM.ai
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to PIM.ai
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should not be subscribed

  Scenario: Failed to unsubscribe a product with an invalid token
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to PIM.ai
    And the PIM.ai token is expired
    When I unsubscribe the product "B00EYZY6AC"
    Then the product "B00EYZY6AC" should be subscribed
