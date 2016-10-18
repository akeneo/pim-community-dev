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

  Scenario: Succesfully assign assets to a product
    Given I am logged in as "Julia"
    And I am on the "shirt" product page
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
    And the rows "dog, akene" should be checked
    And I remove "machine" from the asset basket
    Then the asset basket should contain akene, dog
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain akene, dog
    And I save the product
    Then the "Front view" asset gallery should contain akene, dog

  Scenario: Display assets thumbnails for current scope and locale
    Given I am logged in as "Julia"
    And I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see "akene.jpg"
    And I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I switch the locale to "de_DE"
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see "akene.jpg"
    And I visit the "Variations" tab
    And I switch the locale to "en_US"
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see "akene.jpg"
    And I am on the "shirt" product page
    And I add available attributes Front view
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
    Then the asset basket item "paint" should contain the thumbnail for channel "mobile"
    And the asset basket item "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "de_DE"
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

  Scenario: Do not see the category tree when the user has not the permission
    Given I am logged in as "Peter"
    And I am on the "shirt" product page
    And I add available attributes Front view
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
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    Then I should not see the text "Asset main catalog"
    And I confirm the asset modification
    And I save the product

  @jira https://akeneo.atlassian.net/browse/PIM-5988
  Scenario: Correctly add assets by their code in the basket, even if not in the first 20 assets
    Given the following assets:
      | code      | categories |
      | video_1   | videos     |
      | video_2   | videos     |
      | video_3   | videos     |
      | video_4   | videos     |
      | video_5   | videos     |
      | video_6   | videos     |
      | video_7   | videos     |
      | video_8   | videos     |
      | video_9   | videos     |
      | video_10  | videos     |
      | video_11  | videos     |
      | video_12  | videos     |
      | video_13  | videos     |
      | video_14  | videos     |
      | video_15  | videos     |
      | video_16  | videos     |
      | video_17  | videos     |
      | video_18  | videos     |
      | video_19  | videos     |
      | video_20  | videos     |
      | video_21  | videos     |
    And I am logged in as "Julia"
    And I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
    And I change the page size to 100
    And I check the row "video_20"
    And I check the row "video_21"
    Then the asset basket should contain video_20, video_21
