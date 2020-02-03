Feature: Automatically link assets to products
  In order save time enrinching the assets into the products
  As a user
  I want the assets to be automatically linked to the products upon their creation

  @acceptance-back
  Scenario: A job to link assets to products is launched when we create multiple asset
    Given an asset family with some rule templates
    When I link some assets to some products using this rule template
    Then a job has been launched to link assets to products

  @acceptance-back
  Scenario: A job to link one asset to products is launched when we create one asset
    Given an asset family with some rule templates
    When I link one asset to some products using this rule template
    Then a job has been launched to link asset to products

  @acceptance-back
  Scenario: A job to link one asset to products is NOT launched when we create one asset and the asset family does not have any product link rule
	Given an asset family with no product link rule
	When I link one asset to some products using this rule template
	Then a job has not been launched to link assets to products

  @acceptance-back
  Scenario: A job to link assets to products is NOT launched when we create an asset if the asset family does not have any product link rule
	Given an asset family with no product link rule
	When I link some assets to some products using this rule template
	Then a job has not been launched to link assets to products

  @acceptance-back
  Scenario: A job to link all assets in an asset family is launched when we create one asset
    Given an asset family with some rule templates
    When I link all assets in the asset family to some products using this rule template
    Then a job has been launched to link all assets of the asset family to products
