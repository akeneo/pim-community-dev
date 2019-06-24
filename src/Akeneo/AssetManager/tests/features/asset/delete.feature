Feature: Delete one asset
  In order to administrate assets
  As a user
  I need to delete assets

  @acceptance-back
  Scenario: Deleting a asset
    Given an asset family with one asset
    When the user deletes the asset
    Then there is no exception thrown
    And there is no violations errors
    And the asset should not exist anymore

  @acceptance-back
  Scenario: Deleting a unknown asset
    When the user tries to delete asset that does not exist
    Then an exception is thrown

  @acceptance-front
  Scenario: Deleting a asset
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_create | true |
      | akeneo_assetmanager_asset_edit   | true |
      | akeneo_assetmanager_asset_delete | true |
    When the user deletes the asset
    Then the user should see a success message on the edit page

#  @acceptance-front
  Scenario: Cannot delete a asset without the rights
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_create | true |
      | akeneo_assetmanager_asset_edit   | true  |
      | akeneo_assetmanager_asset_delete | false |
    Then the user should not see the delete button
