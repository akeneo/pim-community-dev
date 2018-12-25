@acceptance-back
Feature: Franklin configuration
  In order to automatically enrich products
  As a system administrator
  I want to configure Franklin

  @end-to-end @javascript @critical
  Scenario: The system administrator successfully configures Franklin
    Given Franklin has not been configured
    When a system administrator configures Franklin using a valid token
    Then Franklin is activated

  Scenario: The system administrator cannot configure Franklin with an invalid token
    Given Franklin has not been configured
    When a system administrator configures Franklin using an invalid token
    Then Franklin is not activated
    And a token invalid message for configuration should be sent

  Scenario: The system administrator configures a new token to replace an expired one
    Given Franklin is configured with an expired token
    When a system administrator configures Franklin using a valid token
    Then Franklin is activated

  Scenario: The system administrator cannot configure Franklin with an invalid token to replace an expired one
    Given Franklin is configured with an expired token
    When a system administrator configures Franklin using an invalid token
    Then Franklin is not activated
    And a token invalid message for configuration should be sent

  Scenario: Dealing with error on activation when Franklin server is down
    Given Franklin has not been configured
    And Franklin server is down
    When a system administrator configures Franklin using a valid token
    Then Franklin is not activated
    And a connection invalid message should be sent
