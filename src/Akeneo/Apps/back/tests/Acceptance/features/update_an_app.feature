@acceptance-back
Feature: Update an App

  Scenario: Successfully update an App
    Given the destination App "Magento" has been created
    When I modify the App "Magento" with:
      | label    | flow_type |
      | Pimgento | other     |
    Then the App "Magento" should exists
    And the App "Magento" label should be Pimgento
    And the App "Magento" flow type should be other
