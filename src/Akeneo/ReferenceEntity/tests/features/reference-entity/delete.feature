Feature: Delete an enriched entity
  In order to keep my enriched entities up to date
  As a user
  I want to delete an enriched entity

  Background:
    Given the following enriched entity:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |

  @acceptance-back
  Scenario: Delete an enriched entity
    When the user deletes the enriched entity "designer"
    Then there should be no enriched entity "designer"

  @acceptance-front
  Scenario: Delete an enriched from the edit view
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_delete | true |
    And the user asks for the enriched entity "designer"
    When the user deletes the enriched entity "designer"
    Then the user should see the deleted notification

  @acceptance-front
  Scenario: Dismiss the deletion of an enriched from the edit view
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_delete | true |
    And the user asks for the enriched entity "designer"
    When the user refuses to delete the current enriched entity
    Then the user should not be notified that deletion has been made

  @acceptance-front
  Scenario: The user can't delete the entity if he doesn't have the permission
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_delete | false |
    And the user asks for the enriched entity "designer"
    Then the user should not see the deletion button

  @acceptance-front
  Scenario: The user is notified if the deletion goes wrong
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_delete | true |
    And the user asks for the enriched entity "designer"
    When the user fails to delete the enriched entity "designer"
    Then the user should see the delete notification error
