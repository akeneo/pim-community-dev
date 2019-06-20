Feature: Delete an asset family
  In order to keep my asset families up to date
  As a user
  I want to delete an asset family

  Background:
    Given a valid asset family
  @acceptance-back
  Scenario: Delete an asset family
    When the user deletes the asset family "designer"
    Then there should be no asset family "designer"

  @acceptance-back
  Scenario: User can't delete an Asset Family if it is used in any Asset Attribute on an Asset Family
    Given the following asset attributes:
      | entity_identifier | code   | labels                                 | required | order | value_per_channel | value_per_locale | asset_type |
      | designer          | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false    | 2     | false             | false            | designer    |
    When the user deletes the asset family "designer"
    Then there should be a validation error on the property '' with message 'You can not delete this entity because asset family attributes are related to this entity'
    And there is an asset family "designer" with:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |

  @acceptance-front
  Scenario: Delete an asset family from the edit view
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_edit   | true |
      | akeneo_assetmanager_asset_family_delete | true |
    And the user asks for the asset family "designer"
    When the user deletes the asset family "designer"
    Then the user should see the deleted notification

  @acceptance-front
  Scenario: Dismiss the deletion of an asset family from the edit view
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_edit   | true |
      | akeneo_assetmanager_asset_family_delete | true |
    And the user asks for the asset family "designer"
    When the user refuses to delete the current asset family
    Then the user should not be notified that deletion has been made

  @acceptance-front
  Scenario: The user can't delete the entity if he doesn't have the permission
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_edit   | true |
      | akeneo_assetmanager_asset_family_delete | false |
    And the user asks for the asset family "designer"
    Then the user should not see the deletion button

  @acceptance-front
  Scenario: The user is notified if the deletion goes wrong
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_edit   | true |
      | akeneo_assetmanager_asset_family_delete | true |
    And the user asks for the asset family "designer"
    When the user fails to delete the asset family "designer"
    Then the user should see the delete notification error
