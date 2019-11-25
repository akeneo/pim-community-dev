@acceptance-back
Feature: Delete an App

  Scenario: Successfully delete an App
    Given the destination App "Magento" has been created
    When I delete the "Magento" App
    Then the App "Magento" should not exist
