@javascript
Feature: Mass edit assets to change their categories
  In order to massively classify assets
  As a product manager
  I need to be able to mass edit asset to change their categories

  Background:
    Given the "clothing" catalog configuration

  @skip
  Scenario: Mass classify several assets with a bulk action from the grid
    Given I am logged in as "Pamela"
    And I am on the assets grid
    And I select rows paint, chicagoskyline and akene
    And I press the "Mass edit assets" button
    And I choose the "Classify assets in categories" operation
    And I press the "Asset main catalog" button
    And I expand the "asset_main_catalog" category
    And I click on the "print" category
    And I confirm mass edit
    And I should be on the assets page
    When I wait for the "classify_assets" job to finish
    And I am on the assets grid
    And I open the category tree
    And I expand the "images" category
    Then I should see the text "Asset main catalog (12)"
    And I should see the text "Images (9)"
    And I should see the text "Other picture (3)"
    And I should see the text "In situ pictures (4)"
    And I should see the text "Print (3)"
    And asset category of "paint" should be "print"
    And asset category of "chicagoskyline" should be "print"
    And asset category of "akene" should be "print"

  Scenario: Mass classify all assets with a bulk action from the grid
    Given I am logged in as "Pamela"
    And I am on the assets grid
    And I select rows paint
    And I select all entities
    And I press the "Mass edit assets" button
    And I choose the "Classify assets in categories" operation
    And I press the "Asset main catalog" button
    And I expand the "asset_main_catalog" category
    And I click on the "audio" category
    And I click on the "client_documents" category
    And I confirm mass edit
    And I should be on the assets page
    When I wait for the "classify_assets" job to finish
    And I am on the assets grid
    Then I should see the text "Images (0)"
    And I should see the text "Audio (15)"
    And I should see the text "Client documents (15)"

  @jira https://akeneo.atlassian.net/browse/PIM-6947
  Scenario: Do not display the product categories on asset mass edit
    Given I am logged in as "Pamela"
    And I am on the assets grid
    And I select rows paint
    And I press the "Mass edit assets" button
    When I choose the "Classify assets in categories" operation
    Then I should not see the text "2014 collection"

  @jira https://akeneo.atlassian.net/browse/PIM-6947
  Scenario: Successfully display all the messages related to mass edit
    Given I am logged in as "Pamela"
    And I am on the assets grid
    And I select rows paint
    And I press the "Mass edit assets" button
    When I choose the "Classify assets in categories" operation
    Then I should see the text "Assets bulk action"
    And I should see the text "Classify 1 asset to categories"
