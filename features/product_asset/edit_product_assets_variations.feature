@javascript
Feature: Edit product assets variations
  In order to enrich the existing product assets
  As an asset manager
  I need to be able to edit product assets variations

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Successfully delete reference file
    Given I am on the "bridge" asset page
    And I visit the "Variations" tab
    Then I should see the Mobile variation upload zone
    And I should see the Tablet variation upload zone
    And I should be able to generate Mobile from reference
    And I should be able to generate Tablet from reference
    And I should see "bridge.jpg"
    Given I delete the reference file
    And I confirm the deletion
    Then I should see the reference upload zone
    And I should see the Mobile variation upload zone
    And I should see the Tablet variation upload zone
    And I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference
    And I should not see "bridge.jpg"

  Scenario: Successfully upload a localized reference file
    When I am on the "winter" asset page
    And I visit the "Variations" tab
    Then I should see the reference upload zone
    And I upload the reference file akeneo.jpg
    When I save the asset
    Then I should see "akeneo.jpg"
    And I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference

  Scenario: Successfully upload a localized variation file
    When I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I switch the locale to "German (Germany)"
    And I upload the Mobile variation file akeneo.jpg
    And I save the asset
    # TODO: Check the file

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4784
  Scenario: Successfully delete variation file
    Given I generate missing variations for asset bridge
    And I am on the "bridge" asset page
    And I visit the "Variations" tab
    Given I delete the Tablet variation file
    And I confirm the deletion
    Then I should be able to generate Tablet from reference
    And I should see the Tablet variation upload zone
    Given I delete the reference file
    And I confirm the deletion
    Then I should not be able to generate Tablet from reference
    And I should see the Tablet variation upload zone

  Scenario: Successfully reset variations files
    Given I generate missing variations for asset bridge
    And I am on the "bridge" asset page
    And I visit the "Variations" tab
    Given I reset variations files
    And I confirm the action
    # TODO: Check the files

  @skip @jira https://akeneo.atlassian.net/browse/PIM-4784
  Scenario: Successfully reset one variation file
    Given I generate missing variations for asset bridge
    And I am on the "bridge" asset page
    And I visit the "Variations" tab
    Given I delete the Mobile variation file
    And I confirm the deletion
    Then I should be able to generate Mobile from reference
    Given I generate Mobile variation from reference
    Then I should be able to generate Mobile from reference
