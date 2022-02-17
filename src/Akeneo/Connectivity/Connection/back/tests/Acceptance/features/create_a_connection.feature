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

  Scenario: Fail to create a Connection with a code and label smaller than 3 chars
    Given no Connection has been created
    When I create the destination Connection "a"
    Then the Connection "a" should not exist
    And there should be 0 Connections
    And I should have been warn the code should be longer than 3 chars
    And I should have been warn the label should be longer than 3 chars

  Scenario: Fail to create a Connection with an empty code and label
    Given no Connection has been created
    When I create the destination Connection ""
    Then the Connection "" should not exist
    And there should be 0 Connections
    And I should have been warn the code should not be empty
    And I should have been warn the label should not be empty

  Scenario: Fail to create a Connection with a code and label longer than 100 chars
    Given no Connection has been created
    When I create the destination Connection "<100chars>"
    Then there should be 0 Connections
    And I should have been warn the code should be smaller than 100 chars
    And I should have been warn the label should be smaller than 100 chars

  Scenario: Fail to create a Connection with a code with incorrect characters
    Given no Connection has been created
    When I create the destination Connection "Magento-2"
    Then the Connection "Magento-2" should not exist
    And there should be 0 Connections
    And I should have been warn the code is invalid

  Scenario: Fail to create a Connection with a wrong flow type
    Given no Connection has been created
    When I create the test Connection "Magento"
    Then the Connection "Magento" should not exist
    And there should be 0 Connections
    And I should have been warn the flow type is invalid
