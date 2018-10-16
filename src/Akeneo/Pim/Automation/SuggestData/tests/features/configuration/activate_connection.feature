@acceptance-back
Feature: PIM.ai configuration
  In order to automatically enrich products
  As a system administrator
  I want to configure PIM.ai

  @end-to-end @javascript
  Scenario: The system administrator successfully configures PIM.ai
    Given PIM.ai has not been configured
    When a system administrator configures PIM.ai using a valid token
    Then PIM.ai is activated

  Scenario: The system administrator cannot configure PIM.ai with an invalid token
    Given PIM.ai has not been configured
    When a system administrator configures PIM.ai using an invalid token
    Then PIM.ai is not activated
    And a token invalid message is sent

  Scenario: The system administrator configures a new token to replace an expired one
    Given PIM.ai is configured with an expired token
    When a system administrator configures PIM.ai using a valid token
    Then PIM.ai is activated

  Scenario: The system administrator cannot configure PIM.ai with an invalid token to replace an expired one
    Given PIM.ai is configured with an expired token
    When a system administrator configures PIM.ai using an invalid token
    Then PIM.ai is not activated
    And a token invalid message is sent
