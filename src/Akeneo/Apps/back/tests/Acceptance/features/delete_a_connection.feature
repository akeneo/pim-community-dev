@acceptance-back
Feature: Delete a Connection

  Scenario: Successfully delete a Connection
    Given the destination Connection "Magento" has been created
    When I delete the "Magento" Connection
    Then the Connection "Magento" should not exist
