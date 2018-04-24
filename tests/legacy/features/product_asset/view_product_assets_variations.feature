@javascript
Feature: View product assets variations
  In order to view an existing product assets
  As a asset manager
  I need to be able to view product assets that I'm not allowed to edit

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  @jira https://akeneo.atlassian.net/browse/PIM-6011
  Scenario: Successfully view an asset
    Given I edit the "images" asset category
    And I visit the "Permissions" tab
    And I fill in "Allowed to edit assets" with "" on the current page
    And I save the category
    When I am on the "bridge" asset page
    Then I should see the text "Product asset / bridge"
