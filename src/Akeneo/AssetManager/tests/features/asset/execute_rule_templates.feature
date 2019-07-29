Feature: Automatically link assets to products
  In order save time enrinching the assets into the products
  As a user
  I want the assets to be automatically linked to the products upon their creation

  @acceptance-back
  Scenario: A job to link assets to products is launched when we create an asset
    Given an asset family with some rule templates
    When I create an asset for this family
    Then a job has been launched to link assets to products

  @acceptance-back
  Scenario: The asset is linked to a product depending on the values of the assets
    Given an asset family with a rule template having a dynamic patterns depending on the asset values
    When I create an asset for this family having values for the dynamic patterns
    Then there is a rule executed to link this asset that takes into account the dynamic values

    # Dynamic values not replaced ?
