Feature: Create a asset
  In order to have assets
  As a user
  I want to create a asset

  Background:
    Given a valid asset family

  @acceptance-back
  Scenario: Creating a asset
    When the user creates a asset "stark" for entity "designer" with:
      | labels                                            |
      | {"en_US": "Tony Stark", "fr_FR": "Thierry Stark"} |
    Then there is a asset with:
      | code  | entity_identifier | labels                                            |
      | stark | designer          | {"en_US": "Tony Stark", "fr_FR": "Thierry Stark"} |

  @acceptance-back
  Scenario: Creating a asset with no labels
    When the user creates a asset "stark" for entity "designer" with:
      | labels |
      | {}     |
    Then there is a asset with:
      | code  | entity_identifier | labels |
      | stark | designer          | {}     |

  @acceptance-back
  Scenario: Cannot create a asset with invalid identifier
    When the user creates a asset "invalid/identifier" for entity "designer" with:
      | labels |
      | {}     |
    Then an exception is thrown with message "Asset code may contain only letters, numbers and underscores. "invalid/identifier" given"
    And there should be no asset

  @acceptance-back
  Scenario: Cannot create more assets for an asset family than the limit
    Given 1000 random assets for an asset family
    When the user creates a asset "stark" for entity "designer" with:
      | labels              |
      | {"en_US": "Starck"} |
    Then there should be a validation error with message 'You cannot create the asset "Starck" because you have reached the limit of 1000 assets for this asset family'

  @acceptance-back
  Scenario: Cannot create an asset if code already exists
    When the user creates a asset "my_code" for entity "designer" with:
      | labels |
      | {}     |
    And the user creates a asset "my_code" for entity "designer" with:
      | labels |
      | {}     |
    Then there should be a validation error with message 'An asset already exists with code "my_code"'

  @acceptance-front
  Scenario: Creating a asset
    When the user asks for the asset family "designer"
    Given the user has the following rights:
      | akeneo_assetmanager_asset_create | true |
    And the user creates a asset of "designer" with:
      | code  | labels             |
      | stark | {"en_US": "Stark"} |
    Then the asset will be saved
    And the user saves the asset

#  @acceptance-front
  Scenario: Creating multiple assets in sequence
    When the user asks for the asset family "designer"
    Given the user has the following rights:
      | akeneo_assetmanager_asset_create | true |
    And the user creates a asset of "designer" with:
      | code  | labels             |
      | stark | {"en_US": "Stark"} |
    Then the asset will be saved
    And the user toggles the sequantial creation
    And the user saves the asset
    Then the asset creation form should be displayed

  @acceptance-front
  Scenario: User doesn't have the right to create a asset
    Given the user has the following rights:
      | akeneo_assetmanager_asset_create | false |
    When the user asks for the asset family "designer"
    Then the user cannot create a asset

#  @acceptance-front
#  Scenario: Cannot create a asset with invalid identifier
#    When the user asks for the asset family "designer"
#    Given the user has the following rights:
#      | akeneo_assetmanager_asset_create | true |
#    And the user creates a asset of "designer" with:
#      | code               | labels |
#      | invalid/identifier | {}     |
#    Then the asset validation error will be "This field may only contain letters, numbers and underscores."
#    And the user saves the asset
#    And a validation message is displayed "This field may only contain letters, numbers and underscores."
