Feature: Delete a reference entity
  In order to keep my reference entities up to date
  As a user
  I want to delete a reference entity

  Background:
    Given a valid reference entity
  @acceptance-back
  Scenario: Delete a reference entity
    When the user deletes the reference entity "designer"
    Then there should be no reference entity "designer"

  @acceptance-back
  Scenario: User can't delete a Reference Entity if it is used in any Record Attribute on a Reference Entity
    Given the following record attributes:
      | entity_identifier | code   | labels                                 | required | order | value_per_channel | value_per_locale | record_type |
      | designer          | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false    | 2     | false             | false            | designer    |
    When the user deletes the reference entity "designer"
    Then there should be a validation error on the property '' with message 'You can not delete this entity because reference entity attributes are related to this entity'
    And there is a reference entity "designer" with:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |

  @acceptance-front
  Scenario: Delete a reference entity from the edit view
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit   | true |
      | akeneo_referenceentity_reference_entity_delete | true |
    And the user asks for the reference entity "designer"
    When the user deletes the reference entity "designer"
    Then the user should see the deleted notification

  @acceptance-front
  Scenario: Dismiss the deletion of a reference entity from the edit view
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit   | true |
      | akeneo_referenceentity_reference_entity_delete | true |
    And the user asks for the reference entity "designer"
    When the user refuses to delete the current reference entity
    Then the user should not be notified that deletion has been made

  @acceptance-front
  Scenario: The user can't delete the entity if he doesn't have the permission
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit   | true |
      | akeneo_referenceentity_reference_entity_delete | false |
    And the user asks for the reference entity "designer"
    Then the user should not see the deletion button

  @acceptance-front
  Scenario: The user is notified if the deletion goes wrong
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit   | true |
      | akeneo_referenceentity_reference_entity_delete | true |
    And the user asks for the reference entity "designer"
    When the user fails to delete the reference entity "designer"
    Then the user should see the delete notification error
