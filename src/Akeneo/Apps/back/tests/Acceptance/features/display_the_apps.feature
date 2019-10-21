@acceptance-back
Feature: Display the Apps

  Scenario: Successfully display when no existing Apps
    Given no App has been created
    When I display the Apps
    Then there should be 0 Apps

  Scenario: Successfully display the existing Apps
    Given the destination App "Magento" has been created
    And the other App "Bynder" has been created
    And the source App "AS 400" has been created
    When I display the Apps
    Then there should be 3 Apps
