Feature: List products linked to a asset
  In order to know which products are linked to a asset
  As a user
  I want see the first selection of products for a asset

  @acceptance-front
  Scenario: Listing linked products
    Given a valid asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_list_product | true |
    When the user asks for the list of linked product
    Then the user should see the list of products linked to the asset

  @acceptance-front
  Scenario: Listing linked products without any asset family attribute
    Given a valid asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_list_product | true |
    When the user asks for the list of linked product without any asset family attribute
    Then the user should not see any linked product attribute

  @acceptance-front
  Scenario: Listing linked products without any linked products
    Given a valid asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_list_product | true |
    When the user asks for the list of linked product without any linked product
    Then the user should not see any linked product
