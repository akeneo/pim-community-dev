@acceptance-back
Feature: PIM.ai configuration
  In order to automatically enrich products
  As a system administrator
  I want to retrieve connection configuration

  Scenario: The system administrator can retrieve his token
    Given PIM.ai is configured with a valid token
    When a system administrator retrieves the PIM.ai configuration
    Then PIM.ai valid token is retrieved

  Scenario: The system administrator can retrieve his expired token
    Given PIM.ai is configured with an expired token
    When a system administrator retrieves the PIM.ai configuration
    Then PIM.ai expired token is retrieved
