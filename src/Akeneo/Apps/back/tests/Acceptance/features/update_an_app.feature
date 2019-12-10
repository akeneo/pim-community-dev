@acceptance-back
Feature: Update an App

  Scenario: Successfully update an App
    Given the destination App "Magento" has been created
    When I modify the App "Magento" with:
      | label    | flow_type | image           |
      | Pimgento | other     | a/b/c/image.jpg |
    Then the App "Magento" should exist
    And the App "Magento" label should be Pimgento
    And the App "Magento" flow type should be other
    And the App "Magento" image should be "a/b/c/image.jpg"

  Scenario: Fail to update an App with an invalid image
    Given the destination App "Magento" has been created
    When I modify the App "Magento" with:
      | label    | flow_type | image    |
      | Pimgento | other     | path.jpg |
    Then I should have been warn that the image does not exist
    And the App "Magento" label should be Magento
    And the App "Magento" flow type should be destination
    And the App "Magento" should not have an image
