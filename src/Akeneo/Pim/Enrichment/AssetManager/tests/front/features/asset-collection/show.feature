Feature: Show an asset collection
  In order to see what assets are in a given collection
  As a user
  I want to be able to see and manipulate an asset collection

  @acceptance-front
  Scenario: See multiple asset collections
    Given an asset collection with three assets
    When the user go to the asset tab
    Then the three assets in the collection be displayed

  @acceptance-front
  Scenario: Remove one asset from the collection
    Given an asset collection with three assets
    When the user go to the asset tab
    And remove an asset
    Then I should only see two remaining assets

  @acceptance-front
  Scenario: Remove all assets from the collection
    Given an asset collection with three assets
    When the user go to the asset tab
    And remove all assets
    Then there should be no asset in the collection

  @acceptance-front
  Scenario: Move one asset in the collection
    Given an asset collection with three assets
    When the user go to the asset tab
    And move an asset
    Then I should only see the reordered assets
