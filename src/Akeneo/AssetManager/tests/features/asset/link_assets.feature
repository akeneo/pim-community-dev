Feature: Automatically link assets to products
  In order save time enrinching the assets into the products
  As a user
  I want the assets to be automatically linked to the products upon their creation

  @acceptance-back
  Scenario: A job to link assets to products is launched when we create an asset
    Given an asset family with some rule templates
    When I link some assets to some products using this rule template
    Then a job has been launched to link assets to products

