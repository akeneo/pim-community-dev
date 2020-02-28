@acceptance-back
Feature: Update a Connection

  Scenario: Successfully update a Connection
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image           | user_role | user_group | auditable |
      | Pimgento | other    | a/b/c/image.jpg | ROLE_API  | API        | yes       |
    Then the Connection "Magento" should exist
    And the Connection "Magento" label should be "Pimgento"
    And the Connection "Magento" flow type should be "other"
    And the Connection "Magento" image should be "a/b/c/image.jpg"
    And the Connection "Magento" user role should be "ROLE_API"
    And the Connection "Magento" user group should be "API"
    And the Connection "Magento" should be auditable

  Scenario: Fail to update a Connection with an invalid image
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image    | user_role | user_group | auditable |
      | Pimgento | other    | path.jpg | ROLE_USER | All        | no        |
    Then I should have been warn that the image does not exist
    And the Connection "Magento" label should be Magento
    And the Connection "Magento" flow type should be destination
    And the Connection "Magento" should not have an image

  Scenario: Fail to update a Connection with an empty label
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image    | user_role | user_group | auditable |
      |          | other    | path.jpg | ROLE_USER | All        | yes       |
    Then the Connection "Magento" should exist
    And I should have been warn the label should not be empty

  Scenario: Fail to update a Connection with a label too long
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label      | flow_type | image    | user_role | user_group | auditable |
      | <100chars> | other    | path.jpg | ROLE_USER | All        | yes       |
    Then the Connection "Magento" should exist
    And I should have been warn the label should be smaller than 100 chars

  Scenario: Fail to update a Connection with a label too small
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label | flow_type | image    | user_role | user_group | auditable |
      | a     | other    | path.jpg | ROLE_USER | All        | yes       |
    Then the Connection "Magento" should exist
    And I should have been warn the label should be longer than 3 chars

  Scenario: Fail to update a Connection with a wrong flow type
    Given the destination Connection "Magento" has been created
    When I modify the Connection "Magento" with:
      | label    | flow_type | image    | user_role | user_group | auditable |
      | Magento  | wrong    | path.jpg | ROLE_USER | All        | yes       |
    Then the Connection "Magento" should exist
    And I should have been warn the flow type is invalid
