@javascript
Feature: Mass edit assets to change their categories
  In order to massively classify assets
  As a product manager
  I need to be able to mass edit asset to change their categories

  Background:
    Given the "clothing" catalog configuration

  Scenario: Mass classify several assets with a bulk action from the grid
    Given I am logged in as "Julia"
    And I am on the assets page
    And I select rows minivan, machine and bridge
    And I press "Classify" on the "Bulk Actions" dropdown button
    And I press the "Asset main catalog" button
    And I expand the "asset_main_catalog" category
    And I click on the "print" category
    And I press the "Next" button
    And I press the "Confirm" button
    And I should be on the assets page
    When I wait for the "asset-classify-move" mass-edit job to finish
    And I am on the assets page
    And I expand the "images" category
    Then I should see the text "Asset main catalog (12)"
    And I should see the text "Images (9)"
    And I should see the text "Other picture (3)"
    And I should see the text "In situ pictures (4)"
    And I should see the text "Print (3)"
    And asset category of "minivan" should be "print"
    And asset category of "machine" should be "print"
    And asset category of "bridge" should be "print"

  Scenario: Mass classify all assets with a bulk action from the grid
    Given I am logged in as "Julia"
    And I am on the assets page
    And I select all entities
    And I press "Classify" on the "Bulk Actions" dropdown button
    And I press the "Asset main catalog" button
    And I expand the "asset_main_catalog" category
    And I click on the "audio" category
    And I click on the "client_documents" category
    And I press the "Next" button
    And I press the "Confirm" button
    And I should be on the assets page
    When I wait for the "asset-classify-move" mass-edit job to finish
    And I am on the assets page
    And I should see the text "Images (0)"
    And I should see the text "Audio (15)"
    And I should see the text "Client documents (15)"
