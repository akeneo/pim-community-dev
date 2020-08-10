Feature: Throw an error if an asset doesn't exist or if it doesn't belong to an asset family
  In order to inform the user
  As a regular user
  I need to be able to see the validation error

  Background:
    Given a list of assets
    And an authentified user
    And the following attributes:
      | code            | type                         |  reference_data_name |
      | sku             | pim_catalog_identifier       |  designer            |
      | assetCollection | pim_catalog_asset_collection |  designer            |
    And the following attribute options:
      | attribute       | code                 |
      | assetCollection | starck               |
      | assetCollection | coco                 |
      | assetCollection | absorb_atmosphere_1  |
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"

  @acceptance-back
  Scenario: Providing an asset that belongs to the asset family should not raise an error
    When a product is created with values:
      | attribute       | data        | scope | locale |
      | assetCollection | starck,coco |       |        |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a non existing asset should raise an error
    When a product is created with values:
      | attribute       | data                 | scope | locale |
      | assetCollection | starck,inventedAsset |       |        |
    Then the error 'Please make sure the "inventedAsset" asset exists and belongs to the "designer" asset family for the "assetCollection" attribute.' is raised

  @acceptance-back
  Scenario: Providing an asset that doesn't belong to the asset family should raise an error
    When a product is created with values:
      | attribute       | data                     | scope | locale |
      | assetCollection | coco,absorb_atmosphere_1 |       |        |
    Then the error 'Please make sure the "absorb_atmosphere_1" asset exists and belongs to the "designer" asset family for the "assetCollection" attribute.' is raised
