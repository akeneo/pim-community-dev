@acceptance-back
Feature: Regenerate a Connection password

  Scenario: Successfully regenerate a Connection password
    Given the destination Connection "Magento" has been created
    When I regenerate the "Magento" Connection password
    Then the "Magento" Connection password should have been changed
