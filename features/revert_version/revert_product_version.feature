@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully revert the status of a product (disabled)
    Given an enabled "boat" product
    And I am on the "boat" product page
    And I disable the product
    And I visit the "History" tab
    And there should be 2 update
    When I click on the "Revert to this version" action of the row which contains "sku: boat"
    Then product "boat" should be enabled

  Scenario: Successfully revert the status of a product (enable)
    Given a disabled "boat" product
    And I am on the "boat" product page
    And I enable the product
    And I visit the "History" tab
    And there should be 2 update
    When I click on the "Revert to this version" action of the row which contains "sku: boat"
    Then product "boat" should be disabled
