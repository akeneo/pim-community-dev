@acceptance-back
Feature: Franklin configuration
  In order to automatically enrich products
  As a system administrator
  I want to retrieve connection configuration

  Scenario: The system administrator can retrieve his token
    Given Franklin is configured with a valid token
    When I retrieve Franklin's configuration
    Then the retrieved token should be valid

  Scenario: The system administrator can retrieve his expired token
    Given Franklin is configured with an expired token
    When I retrieve Franklin's configuration
    Then the retrieved token should be expired

  Scenario: The system administrator can retrieve his configuration when there is no token defined
    Given Franklin has not been configured
    When I retrieve Franklin's configuration
    Then no token should be retrieved
