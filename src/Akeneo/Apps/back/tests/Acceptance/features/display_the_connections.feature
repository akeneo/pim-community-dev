@acceptance-back
Feature: Display the Connections

  Scenario: Successfully display when no existing Connection
    Given no Connection has been created
    When I display the Connections
    Then there should be 0 Connections

  Scenario: Successfully display the existing Connections
    Given the destination Connection "Magento" has been created
    And the other Connection "Bynder" has been created
    And the source Connection "AS 400" has been created
    When I display the Connections
    Then there should be 3 Connections
