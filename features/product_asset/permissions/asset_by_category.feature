@javascript
Feature: Count asset by category in a tree
  In order to count asset by category
  As a asset manager
  I need to show count of asset by category

  Background:
    Given the "clothing" catalog configuration
    And the following assets:
      | code     | categories          |
      | doc_tech | technical_documents |

  Scenario: Show assets associated to categories as an administrator
    Given I am logged in as "Peter"
    And I am on the assets grid
    And I expand the "images" category
    And I should see the text "Asset main catalog (13)"
    And I should see the text "Images (12)"
    And I should see the text "Other picture (5)"
    And I should see the text "In situ pictures (5)"
    And I should see the text "Technical documents (1)"
    And I should not see "Archives"

  Scenario: Show assets associated to categories as a manager
    Given I am logged in as "Julia"
    And I am on the assets grid
    And I should see the text "Asset main catalog (12)"
    And I expand the "images" category
    And I should see the text "Images (12)"
    And I should see the text "Other picture (5)"
    And I should see the text "In situ pictures (5)"
    And I should not see "Technical documents"

  Scenario: Show assets associated to categories as a redactor
    Given I am logged in as "Sandra"
    And I am on the assets grid
    And I expand the "images" category
    And I should see the text "Asset main catalog (8)"
    And I should see the text "Images (8)"
    And I should see the text "Other picture (5)"
    And I should not see "In situ pictures"
