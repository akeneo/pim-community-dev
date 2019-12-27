@acceptance-back
Feature: Create a Connection

  Scenario: Successfully create a Connection
    Given no Connection has been created
    When I create the destination Connection "Magento"
    Then the Connection "Magento" should exist
    And the Connection "Magento" label should be Magento
    And the Connection "Magento" flow type should be destination
    And there should be 1 Connections

  Scenario: Fail to create a Connection that already exists
    Given the destination Connection "Magento" has been created
    When I create the destination Connection "Magento"
    Then the Connection "Magento" should exist
    And there should be 1 Connections
    And I should have been warn that the code is unique
