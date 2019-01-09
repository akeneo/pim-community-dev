@acceptance-back
Feature: Fetch connection status
  In order to know the current status for Franklin connection
  As the System
  I want to fetch connection status

  Scenario: I retrieve the connection status
    Given Franklin is configured with a valid token
    And the predefined attributes asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When I retrieve the connection status
    Then Franklin connection status should be activated
    And Franklin connection status should be valid
    And the identifiers mapping should be valid
    And there should have 0 product subscribed to Franklin

  Scenario: I retrieve the connection status without having configured it
    When I retrieve the connection status
    Then Franklin connection status should not be activated
    And the identifiers mapping should not be valid
    And Franklin connection status should not be valid

  Scenario: I retrieve the connection status with an expired token
    Given Franklin is configured with an expired token
    When I retrieve the connection status
    Then Franklin connection status should be activated
    And Franklin connection status should not be valid

  Scenario: I retrieve the connection status when Franklin server is down
    Given Franklin is configured with a valid token
    And Franklin server is down
    When I retrieve the connection status
    Then Franklin connection status should be activated
    And Franklin connection status should not be valid

  Scenario: I retrieve the connection status with the subscriptions count
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "606449099812" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And the product "B00EYZY6AC" is subscribed to Franklin
    And the product "606449099812" is subscribed to Franklin
    When I retrieve the connection status
    Then there should have 2 products subscribed to Franklin
