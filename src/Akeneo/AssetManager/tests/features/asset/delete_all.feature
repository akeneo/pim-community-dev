Feature: Delete all asset family asset
  In order to administrate assets
  As a user
  I need to delete all assets belonging to a refenrence entity

  @acceptance-back
  Scenario: Deleting all assets of an asset family
    Given two asset families with two assets each
    When the user deletes all the assets from one asset family
    Then there should be no assets for this asset family
    But there is still two assets on the other asset family

  @acceptance-back
  Scenario: Deleting all assets of an unknown asset family
    Given two asset families with two assets each
    When the user deletes all the assets from an unknown entity
    And there is still two assets for each asset family

  @acceptance-front
  Scenario: Delete all asset family assets
    Given a valid asset family
    And the following assets for the asset family "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    And the user has the following rights:
      | akeneo_assetmanager_asset_create      | true |
      | akeneo_assetmanager_asset_edit        | true |
      | akeneo_assetmanager_assets_delete_all | true |
    And the user asks for the asset family "designer"
    When the user deletes all the asset family assets
    Then the user should see the successfull deletion notification

  @acceptance-front
  Scenario: Error while deleting all asset family assets
    Given a valid asset family
    And the following assets for the asset family "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    And the user has the following rights:
      | akeneo_assetmanager_asset_create      | true |
      | akeneo_assetmanager_asset_edit        | true |
      | akeneo_assetmanager_assets_delete_all | true |
    And the user asks for the asset family "designer"
    When the user cannot delete all the asset family assets
    Then the user should see the failed deletion notification

  @acceptance-front
  Scenario: Cannot delete all asset family assets without rights
    Given a valid asset family
    And the following assets for the asset family "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    And the user has the following rights:
      | akeneo_assetmanager_asset_create      | true  |
      | akeneo_assetmanager_asset_edit        | true  |
      | akeneo_assetmanager_assets_delete_all | false |
    And the user asks for the asset family "designer"
    Then the user should not see the delete all button
