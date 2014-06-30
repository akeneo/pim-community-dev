Feature: Update user preferences
  In order for users to be able to choose their preferences
  As an administrator
  I need to synchronize user preferences with the catalog configuration

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully delete a tree used by a user
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see "Default tree"
    And I should see "2013 collection"
    When I edit the "tablet" channel
    And I change the "Category tree" to "2014 collection"
    And I save the channel
    And I edit the "2013_collection" category
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see "Default tree"
    And I should see "2014 collection"
    And I should not see "2013 collection"

  @javascript
  Scenario: Successfully delete a channel used by a user
    Given I edit the "Peter" user
    And I visit the "Additional" tab
    Then I should see "Catalog scope"
    And I should see "Print"
    When I edit the "Print" channel
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Peter" user
    And I visit the "Additional" tab
    Then I should see "Catalog scope"
    And I should see "Ecommerce"
    And I should not see "Print"

  @javascript
  Scenario: Successfully disable a locale used by a user
    Given I edit the "Julia" user
    And I visit the "Additional" tab
    And I change the "Catalog locale" to "fr_FR"
    And I save the user
    When I visit the "Additional Information" tab
    Then I should see "Catalog locale"
    And I should see "fr_FR"
    When I edit the "ecommerce" channel
    And I press the "Delete" button
    And I confirm the deletion
    And I edit the "Julia" user
    And I visit the "Additional" tab
    Then I should see "Catalog locale"
    And I should see "de_DE"
    And I should not see "fr_FR"
