@acceptance-back
Feature: Regenerate a Connection secret

  Scenario: Successfully regenerate a Connection secret
    Given the destination Connection "Magento" has been created
    When I regenerate the "Magento" Connection secret
    Then the "Magento" Connection secret should have been changed
