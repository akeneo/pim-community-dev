@javascript
Feature: Edit a category
  In order to be able to modify the category tree
  As a product manager
  I need to be able to edit a category

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit a category
    Given I edit the "Sandals" category
    Then I should see the Code field
    And the field Code should be disabled
    When I fill the input labelled 'English' with 'My sandals'
    And I press the "Save" button
    Then I should see the text "Category successfully updated"
    And I should see the text "My sandals"
