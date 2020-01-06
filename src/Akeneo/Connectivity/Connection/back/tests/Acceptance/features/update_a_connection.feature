@acceptance-back
Feature: Update a Connection

  Scenario: Successfully update a Connection
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image           | user_role | user_group |
      | Pimgento | other     | a/b/c/image.jpg | User      | All        |
    Then the Connection "Magento" should exist
    And the Connection "Magento" label should be Pimgento
    And the Connection "Magento" flow type should be other
    And the Connection "Magento" image should be "a/b/c/image.jpg"

  Scenario: Fail to update a Connection with an invalid image
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image    | user_role | user_group |
      | Pimgento | other     | path.jpg | User      | All        |
    Then I should have been warn that the image does not exist
    And the Connection "Magento" label should be Magento
    And the Connection "Magento" flow type should be destination
    And the Connection "Magento" should not have an image
