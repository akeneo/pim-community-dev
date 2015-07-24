@javascript
Feature: Remove product assets
  In order to remove product assets
  As a product manager
  I need to be able to remove asset from its edit page

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku   |
      | shirt |
    And I am logged in as "Julia"

  Scenario: Successfully delete product asset
    Given I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I check the row "chicagoskyline"
    And I check the row "akene"
    And I confirm the asset modification
    And I save the product
    When I am on the "chicagoskyline" asset page
    And I press the "Delete" button
    Then I should see "Delete confirmation"
    And I confirm the removal
    Then I should be on the assets page
    And I should not see asset chicagoskyline
    Then I am on the "shirt" product page
    And I visit the "Media" group
    And the "Front view" asset gallery should contains akene

  Scenario: Remove an asset is forbidden if the asset is used in a published product
    Given I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I check the row "chicagoskyline"
    And I check the row "akene"
    And I confirm the asset modification
    And I save the product
    And I publish the product "shirt"
    When I am on the "chicagoskyline" asset page
    And I press the "Delete" button
    Then I should see "Delete confirmation"
    And I confirm the removal
    Then I should be on the "chicagoskyline" asset edit page
    When I am on the assets page
    And I should see asset chicagoskyline
