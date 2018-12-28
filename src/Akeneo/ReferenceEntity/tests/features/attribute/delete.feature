Feature: Delete an attribute linked to a reference entity
  In order to modify a reference entity
  As a user
  I want delete an attribute linked to a reference entity

  @acceptance-back
  Scenario: Delete a text attribute linked to a reference entity
    Given a valid reference entity
    And the following text attributes:
      | entity_identifier | code | labels                                    | required | order | value_per_channel | value_per_locale | max_length |
      | designer          | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true     | 0     | true              | false            | 44         |
    When the user deletes the attribute "name" linked to the reference entity "designer"
    Then there is no attribute "name" for the reference entity "designer"

  @acceptance-front
  Scenario: Delete a text attribute linked to a reference entity
    Given a valid reference entity
    And the user has the following rights:
      | akeneo_referenceentity_attribute_edit   | true |
      | akeneo_referenceentity_attribute_delete | true |
    And the following attributes for the reference entity "designer":
      | code     | type  | labels                                  |
      | name     | text  | {"en_US": "Name", "fr_FR": "Name"}      |
      | portrait | image | {"en_US": "Portrait", "fr_FR": "Image"} |
    And the user asks for the reference entity "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | image |
    When the user deletes the attribute "name" linked to the reference entity "designer"
    And the user should see the deleted notification
    Then there should be the following attributes:
      | code | type |
      | name | text |

  @acceptance-front
  Scenario: Cannot delete a text attribute linked to a reference entity
    Given a valid reference entity
    And the user has the following rights:
      | akeneo_referenceentity_attribute_edit   | true |
      | akeneo_referenceentity_attribute_delete | true |
    And the user asks for the reference entity "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | image |
    When the user cannot delete the attribute "name" linked to the reference entity "designer"
    Then the user should see the delete notification error
    And there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | image |

  @acceptance-front
  Scenario: User doesn't have the right to delete a text attribute linked to a reference entity
    Given a valid reference entity
    And the user has the following rights:
      | akeneo_referenceentity_attribute_edit   | false |
      | akeneo_referenceentity_attribute_delete | false |
    And the user asks for the reference entity "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | image |
    Then the user cannot delete the attribute "name"

  @acceptance-front
  Scenario: Cancel a text attribute deletion
    Given a valid reference entity
    And the user has the following rights:
      | akeneo_referenceentity_attribute_edit   | true |
      | akeneo_referenceentity_attribute_delete | true |
    And the user asks for the reference entity "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | image |
    When the user cancel the deletion of attribute "portrait"
    Then the user should not see the delete notification
    And there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | portrait | image |
