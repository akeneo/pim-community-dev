Feature: Validate asset multiple link attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for asset multiple link attribute

  @acceptance-back
  Scenario: Validate the max assets limit constraint of asset multiple link attribute
    Given an asset family
    And there are more than 50 assets in this asset family
    And the following attribute:
      | code      | type                       | reference_data_name |
      | sku       | pim_catalog_identifier     |                     |
      | my_assets | akeneo_asset_multiple_link | designer            |
    And the following family:
      | code      |
      | my_family |
    And the family has the "my_assets" attribute
    And a product in this family
    And this product has more than 50 assets in its asset collection
    When this product is validated
    Then the error "You have reached the limit of 50 assets per collection, you can no longer add assets to your collection." is raised
