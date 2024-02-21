@javascript
Feature: Edit an attribute group
  In order to manage existing attribute groups in the catalog
  As a product manager
  I need to be able to edit an attribute group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit an attribute group
    Given I am on the "sizes" attribute group page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My sizes |
    And I press the "Save" button
    Then I should see the text "My sizes"
