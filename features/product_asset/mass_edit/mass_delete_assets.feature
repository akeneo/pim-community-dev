@javascript
Feature: Mass delete assets
  In order to massively delete assets
  As a product manager
  I need to be able to mass delete several or all assets from the asset datagrid

  Background:
    Given the "clothing" catalog configuration
    And I am logged in as "Pamela"
    And I am on the assets grid

  Scenario: Successfully mass delete many assets
    Given I select rows paint, chicagoskyline and akene
    And I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected assets?"
    When I confirm the removal
    Then I should not see assets paint, chicagoskyline and akene
    And the grid should contain 12 elements

  Scenario: Successfully mass delete one asset
    Given I select rows paint
    When I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected assets?"
    When I confirm the removal
    Then I should not see assets paint
    And the grid should contain 14 elements

  Scenario: Successfully mass delete visible assets
    Given I sort by "code" value ascending
    And I select rows paint
    And I select all visible entities
    When I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected assets?"
    When I confirm the removal
    Then the grid should contain 0 elements

  Scenario: Successfully mass delete all assets
    Given I select rows paint
    And I select all entities
    When I press the "Delete" button
    Then I should see the text "Are you sure you want to delete the selected assets?"
    When I confirm the removal
    Then the grid should contain 0 elements
