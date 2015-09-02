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
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
    And I change the page size to 100
    And I check the row "paint"
    And I check the row "machine"
    Then the asset basket should contain paint, machine
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain paint, machine
    And I save the product
    Then the "Front view" asset gallery should contain paint, machine
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I uncheck the row "paint"
    And I check the row "dog"
    And I check the row "akene"
    And I remove "machine" from the asset basket
    Then the asset basket should contain akene, dog
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain akene, dog
    And I save the product
    Then the "Front view" asset gallery should contain akene, dog

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
    When I check the row "paint"
    And I check the row "chicagoskyline"
    Then the asset basket item "paint" should contain the thumbnail for channel "mobile"
    And the asset basket item "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "de_DE"
    When I confirm the asset modification
    Then the "[front_view]" asset gallery item "paint" should contain the thumbnail for channel "mobile"
    Then the "[front_view]" asset gallery item "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "de_DE"

  Scenario: Successfully filter product assets by category in asset picker
    Given I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    When I select the "Asset main catalog" tree
    Then the grid should contain 15 elements
    When I disable the inclusion of sub-categories
    And I expand the "Images" category
    Then I should be able to use the following filters:
      | filter         | value  | result                                          |
      | asset category | images | paint, chicagoskyline, akene, autumn and bridge |
      | asset category | other  | autumn, bridge, dog, eagle and machine          |
      | asset category | situ   | paint, man_wall, minivan, mouette and mountain  |
    When I enable the inclusion of sub-categories
    Then I should be able to use the following filters:
      | filter         | value  | result                                                                                                     |
      | asset category | images | paint, chicagoskyline, akene, autumn, bridge, dog, eagle, machine, man_wall, minivan, mouette and mountain |
    When I filter by "asset category" with value "unclassified"
    Then I should see assets mugs, photo and tiger
