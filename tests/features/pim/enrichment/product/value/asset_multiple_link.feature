Feature: Validate asset multiple link attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for asset multiple link attribute

  Background:
    Given an authentified user

  @acceptance-back
  Scenario:  Cannot add assets into an asset collection if it reaches the limit of assets per collection
    Given an asset family
    And there are more than 50 assets in this asset family
    And the following attribute:
      | code      | type                         | reference_data_name |
      | sku       | pim_catalog_identifier       |                     |
      | my_assets | pim_catalog_asset_collection | designer            |
    And the following family:
      | code      |
      | my_family |
    And the family has the "my_assets" attribute
    And a product in this family
    When the user adds more than 50 assets to the asset collection
    Then the error "You have reached the limit of 50 assets per collection, you can no longer add assets to your collection." is raised on validation
