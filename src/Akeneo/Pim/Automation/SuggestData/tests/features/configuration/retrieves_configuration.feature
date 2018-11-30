@acceptance-back
Feature: Franklin configuration
  In order to automatically enrich products
  As a system administrator
  I want to retrieve connection configuration

  Scenario: The system administrator can retrieve his token
    Given Franklin is configured with a valid token
    When a system administrator retrieves the Franklin configuration
    Then Franklin valid token is retrieved

  Scenario: The system administrator can retrieve his expired token
    Given Franklin is configured with an expired token
    When a system administrator retrieves the Franklin configuration
    Then Franklin expired token is retrieved

    #token = nul
#  Scenario: The system administrator can retrieve his configuration when there is no token defined
#    Given Franklin is not activated
#    When a system administrator retrieves the Franklin configuration
#    Then Franklin should not retrieve any token
