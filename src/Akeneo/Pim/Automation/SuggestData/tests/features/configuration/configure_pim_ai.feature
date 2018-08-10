@acceptance-back
Feature: PIM.ai configuration
  In order to automatically enrich products
  As a system administrator
  I want to configure PIM.ai

  Scenario: The system administrator successfully configures PIM.ai
    Given PIM.ai has not been configured
    When a system administrator configures PIM.ai using a valid token
    Then PIM.ai is activated

  Scenario: The system administrator cannot configure PIM.ai with an invalid token
    Given PIM.ai has not been configured
    When a system administrator configures PIM.ai using an invalid token
    Then PIM.ai is not activated

  Scenario: The system administrator configures a new token to replace an expired one
    Given PIM.ai is configured with an expired token
    When a system administrator configures PIM.ai using a valid token
    Then PIM.ai is activated

  Scenario: The system administrator cannot configure PIM.ai with an invalid token to replace an expired one
    Given PIM.ai is configured with an expired token
    When a system administrator configures PIM.ai using an invalid token
    Then PIM.ai is not activated

  Scenario: The system administrator can retrieve his token
    Given PIM.ai is configured with a valid token
    When a system administrator retrieves the PIM.ai configuration
    Then PIM.ai valid token is retrieved

  Scenario: The system administrator can retrieve his expired token
    Given PIM.ai is configured with an expired token
    When a system administrator retrieves the PIM.ai configuration
    Then PIM.ai expired token is retrieved
