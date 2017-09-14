@javascript
Feature: Remove product assets
  In order to remove product assets
  As an asset manager
  I need to be able to remove asset from its edit page

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku   | family |
      | shirt | tees   |
    And I am logged in as "Pamela"

  @skip @info To be fixed in TIP-684
  Scenario: Successfully delete product asset
    Given I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I check the row "chicagoskyline"
    And I check the row "akene"
    And I confirm the asset modification
    And I save the product
    When I am on the "chicagoskyline" asset page
    And I press the "Delete" button and wait for modal
    Then I should see the text "Delete confirmation"
    And I confirm the removal
    Then I should be on the assets page
    And I should not see asset chicagoskyline
    Then I am on the "shirt" product page
    And I visit the "Media" group
    And the "Front view" asset gallery should contain akene

  Scenario: Remove an asset is forbidden if the asset is used in a published product
    Given I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I check the row "chicagoskyline"
    And I check the row "akene"
    And I confirm the asset modification
    And I save the product
    And I press the secondary action "Publish"
    And I confirm the publishing
    When I am on the "chicagoskyline" asset page
    And I press the "Delete" button and wait for modal
    Then I should see the text "Delete confirmation"
    And I confirm the removal
    Then I should be on the "chicagoskyline" asset edit page
    When I am on the assets grid
    And I should see asset chicagoskyline
