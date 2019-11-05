@acceptance-back
Feature: Find an App

  Scenario: Successfully display the existing App
    Given the destination App "Magento" has been created
    When I find the App Magento
    Then the App Magento should have credentials
