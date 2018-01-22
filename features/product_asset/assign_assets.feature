@javascript
Feature: Assign assets to a product
  In order to assign assets to a product
  As a product manager
  I need to be able to link multiple assets to a product

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   | family |
      | shirt | tees   |
    And I generate missing variations

  Scenario: Succesfully assign assets to a product
    Given I am logged in as "Julia"
    And I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
    And I check the row "paint"
    And I check the row "chicagoskyline"
    Then the item picker basket should contain paint, chicagoskyline
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain paint, chicagoskyline
    And I save the product
    Then the "Front view" asset gallery should contain paint, chicagoskyline
    And I start to manage assets for "Front view"
    And I uncheck the row "paint"
    And I check the row "autumn"
    And I check the row "akene"
    And the rows "autumn, akene" should be checked
    And I remove "chicagoskyline" from the basket
    Then the item picker basket should contain akene, autumn
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain akene, autumn
    And I save the product
    Then the "Front view" asset gallery should contain akene, autumn

  Scenario: Display assets thumbnails for current scope and locale
    Given I am logged in as "Julia"
    And I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see the text "akene.jpg"
    And I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I switch the locale to "de_DE"
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see the text "akene.jpg"
    And I visit the "Variations" tab
    And I switch the locale to "en_US"
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see the text "akene.jpg"
    And I am on the "shirt" product page
    And I visit the "Media" group
    When I start to manage assets for "Front view"
    Then the row "paint" should contain the thumbnail for channel "tablet"
    And the row "chicagoskyline" should contain the thumbnail for channel "tablet" and locale "en_US"
    When I cancel the asset modification
    And I switch the scope to "mobile"
    And I switch the locale to "de_DE"
    And I start to manage assets for "Vorderansicht"
    Then the row "paint" should contain the thumbnail for channel "mobile"
    And the row "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "de_DE"
    When I check the row "paint"
    And I check the row "chicagoskyline"
    When I confirm the asset modification
    Then the "Vorderansicht" asset gallery item "paint" should contain the thumbnail for channel "mobile"
    Then the "Vorderansicht" asset gallery item "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "de_DE"

  @skip @info Unskip when Firefox will be updated on CI workers
  Scenario: Successfully filter product assets by category in asset picker
    Given I am logged in as "Julia"
    And I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    When I select the "Asset main catalog" tree
    Then the grid should contain 15 elements
    When I disable the inclusion of sub-categories
    And I expand the "Images" category
    Then I should be able to use the following filters:
      | filter         | operator | value  | result                                          |
      | asset category |          | images | paint, chicagoskyline, akene, autumn and bridge |
      | asset category |          | other  | autumn, bridge, dog, eagle and machine          |
      | asset category |          | situ   | paint, man_wall, minivan, mouette and mountain  |
    When I enable the inclusion of sub-categories
    Then I should be able to use the following filters:
      | filter         | operator | value  | result                                                                                                     |
      | asset category |          | images | paint, chicagoskyline, akene, autumn, bridge, dog, eagle, machine, man_wall, minivan, mouette and mountain |
    When I filter by "asset category" with value "unclassified"
    Then I should see assets mugs, photo and tiger

  @skip-nav @info Unskip when the role controller won't need to reload the page anymore
  Scenario: Do not show the category tree when the user has not the permission
    Given I am logged in as "Peter"
    And I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    Then I should see the text "Asset main catalog"
    And I confirm the asset modification
    And I save the product
    Then I should not see the text "There are unsaved changes."
    When I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource List asset categories in the asset picker
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "shirt" product page
    And I start to manage assets for "Front view"
    Then I should not see the text "Asset main catalog"

  @jira https://akeneo.atlassian.net/browse/PIM-5988
  Scenario: Correctly add assets by their code in the basket, even if not in the first 20 assets
    Given the following assets:
      | code     | categories |
      | video_1  | videos     |
      | video_2  | videos     |
      | video_3  | videos     |
      | video_4  | videos     |
      | video_5  | videos     |
      | video_6  | videos     |
      | video_7  | videos     |
      | video_8  | videos     |
      | video_9  | videos     |
      | video_10 | videos     |
    And I am logged in as "Julia"
    And I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
    And I check the row "video_9"
    And I check the row "video_10"
    Then the item picker basket should contain video_9, video_10

  @jira https://akeneo.atlassian.net/browse/PIM-7110
  Scenario: Successfully display asset collection first thumbnail on product grid
    Given I am logged in as "Julia"
    And I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    When I check the row "paint"
    And I check the row "chicagoskyline"
    Then the asset basket should contain chicagoskyline, paint
    When I confirm the asset modification
    Then the "Front view" asset gallery should contain chicagoskyline, paint
    And I save the product
    When I am on the products grid
    And I display in the products grid the columns sku, name, front view
    Then the cell "Front view" in row "shirt" should contain the thumbnail for asset "chicagoskyline" with channel "tablet" and locale "en_US"
