@javascript @skip
Feature: Display a variant group
  In order to display information about a variant group
  As a product manager
  I need to have access to a variant group information and properties

  Background:
    Given a "footwear" catalog configuration

  Scenario: Successfully display axis of variation of a variant group
    Given I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Properties" tab
    Then I should see the text "color size"
