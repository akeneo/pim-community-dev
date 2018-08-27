@javascript
Feature: Display assets thumbnails
  In order to enrich my catalog
  As a regular user
  I need to see the thumbnails of assets

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"
    And I am on the "paint" asset page
    And I upload the reference file paint.jpg
    And I save the asset
    And I am on the "chicagoskyline" asset page
    And I switch the locale to "de_DE"
    And I upload the reference file chicagoskyline-de.jpg
    And I save the asset
    And I am on the "chicagoskyline" asset page
    And I switch the locale to "en_US"
    And I upload the reference file chicagoskyline-en.jpg
    And I save the asset
    And the following products:
      | sku       | family |
      | pineapple | tees   |
      | potatoe   | tees   |
    And I logout
    And I am logged in as "Julia"

  Scenario: Successfully display thumbnails of assets linked to products in products grid
    Given I am on the "pineapple" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I check the row "paint"
    And I check the row "chicagoskyline"
    And I confirm the asset modification
    And I save the product
    And I am on the "potatoe" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I check the row "chicagoskyline"
    And I confirm the asset modification
    And I save the product
    When I am on the products grid
    And I display the columns SKU and Front view
    Then the cell "Front view" in row "pineapple" should contain the thumbnail for channel "tablet"
    And the cell "Front view" in row "potatoe" should contain the thumbnail for channel "tablet" and locale "en_US"
    When I switch the locale to "de_DE"
    And I switch the scope to "mobile"
    Then the cell "Vorderansicht" in row "pineapple" should contain the thumbnail for channel "mobile"
    And the cell "Vorderansicht" in row "potatoe" should contain the thumbnail for channel "mobile" and locale "de_DE"
