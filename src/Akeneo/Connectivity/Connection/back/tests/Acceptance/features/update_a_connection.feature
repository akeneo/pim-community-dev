@acceptance-back
Feature: Update a Connection

  Scenario: Successfully update a Connection
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image           | user_role | user_group |
      | Pimgento | other     | a/b/c/image.jpg | ROLE_API | API        |
    Then the Connection "Magento" should exist
    And the Connection "Magento" label should be "Pimgento"
    And the Connection "Magento" flow type should be "other"
    And the Connection "Magento" image should be "a/b/c/image.jpg"
    And the Connection "Magento" user role should be "ROLE_API"
    And the Connection "Magento" user group should be "API"

  Scenario: Fail to update a Connection with an invalid image
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image    | user_role | user_group |
      | Pimgento | other     | path.jpg | ROLE_USER | All        |
    Then I should have been warn that the image does not exist
    And the Connection "Magento" label should be Magento
    And the Connection "Magento" flow type should be destination
    And the Connection "Magento" should not have an image
