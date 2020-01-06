@acceptance-back
Feature: Find a Connection

  Scenario: Successfully display the existing Connection
    Given the destination Connection "Magento" has been created
    When I find the Connection Magento
    Then the Connection Magento should have credentials
