@acceptance-back
Feature: Create an App

  Scenario: Successfully create an App
    Given no App has been created
    When I create the destination App "Magento"
    Then the App "Magento" should exists
    And there should be 1 Apps

  Scenario: Fail to create an App that already exists
    Given the destination App "Magento" has been created
    When I create the destination App "Magento"
    Then the App "Magento" should exists
    And there should be 1 Apps
    And I should have been warn that the code must be unique
