@javascript
Feature: Assign assets to a product
  In order to assign assets to a product
  As a product manager
  I need to be able to link multiple assets to a product

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   |
      | shirt |
    And I generate missing variations
    And I am logged in as "Julia"

  Scenario: Succesfully assign assets to a product
    Given I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I check the row "paint"
    And I check the row "machine"
    Then the asset basket should contain paint, machine
    And I confirm the asset modification
    Then the "Front view" asset gallery should contains paint, machine
    And I save the product
    Then the "Front view" asset gallery should contains paint, machine
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I uncheck the row "paint"
    And I check the row "dog"
    And I check the row "akene"
    And I remove "machine" from the asset basket
    Then the asset basket should contain akene, dog
    And I confirm the asset modification
    Then the "Front view" asset gallery should contains akene, dog
    And I save the product
    Then the "Front view" asset gallery should contains akene, dog

  Scenario: Display assets thumbnails for current scope and locale
    Given I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I switch the locale to "German (Germany)"
    And I upload the reference file akene.jpg
    And I save the asset
    And I visit the "Variations" tab
    And I switch the locale to "English (United States)"
    And I upload the reference file akene.jpg
    And I save the asset
    And I am on the "shirt" product page
    And I add available attributes Front view
    When I start to manage assets for "Front view"
    Then the row "paint" should contain the thumbnail for channel "tablet"
    And the row "chicagoskyline" should contain the thumbnail for channel "tablet" and locale "en_US"
    When I cancel the asset modification
    And I switch the scope to "mobile"
    And I switch the locale to "de_DE"
    And I start to manage assets for "[front_view]"
    Then the row "paint" should contain the thumbnail for channel "mobile"
    And the row "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "de_DE"
