Feature: Set permissions for an asset family
  In order to manage which user group is able to edit an asset family
  As a catalog manager
  I want to set the permissions of an asset family

  @acceptance-back @acceptance-front @nominal
  Scenario: Set permissions for an asset family and multiple user groups
    Given an asset family
    And the user has the following rights:
      | akeneo_assetmanager_asset_family_manage_permission | true |
    When the user sets the following permissions for the asset family:
      | user_group_identifier | right_level |
      | IT support            | view        |
      | Catalog Manager       | edit        |
    Then there should be a 'view' permission right for the user group 'IT support' on the asset family
    And there should be a 'edit' permission right for the user group 'Catalog Manager' on the asset family

  @acceptance-front
  Scenario: I get information if there is no user groups
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_manage_permission | true |
    When the user ask for an asset family without any user groups
    Then the user should be warned that he needs to create user groups first
