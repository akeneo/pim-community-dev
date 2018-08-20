@javascript
Feature: Edit product assets variations
  In order to enrich the existing product assets
  As a asset manager
  I need to be able to edit product assets variations

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Successfully delete reference file
    Given I am on the "bridge" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should see the flash message "Variation files have been generated successfully."
    And I delete the reference file
    And I confirm the deletion
    Then I should see the reference upload zone
    And I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference

  Scenario: Successfully upload a localized reference file
    When I am on the "dog" asset page
    And I visit the "Variations" tab
    Then I should see the reference upload zone
    And I upload the reference file akeneo.jpg
    When I save the asset
    And I should see the flash message "Variation files have been generated successfully."
    Then I should see the text "akeneo.jpg"
    And I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference

  Scenario: Successfully upload a localized variation file
    When I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I switch the locale to "de_DE"
    And I upload the Mobil variation file chicagoskyline-de.jpg
    And I save the asset
    # TODO: Check the file

  Scenario: Successfully delete variation file
    Given I am on the "bridge" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should see the flash message "Variation files have been generated successfully."
    Given I delete the Tablet variation file
    And I confirm the deletion
    Then I should be able to generate Tablet from reference
    And I should see the Tablet variation upload zone
    Given I delete the reference file
    And I confirm the deletion
    Then I should not be able to generate Tablet from reference
    And I should see the Tablet variation upload zone

  Scenario: Successfully reset variations files
    Given I am on the "bridge" asset page
    And I visit the "Variations" tab
    When I upload the reference file akeneo (copy).jpg
    And I save the asset
    Then I should see the flash message "Variation files have been generated successfully."
    When I reset variations files
    And I confirm the action
    Then I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference

  Scenario: Successfully reset one variation file
    Given I am on the "bridge" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should see the flash message "Variation files have been generated successfully."
    Given I delete the Mobile variation file
    And I confirm the deletion
    Then I should be able to generate Mobile from reference
    Given I generate Mobile variation from reference
    Then I should not be able to generate Mobile from reference

  Scenario: Successfully check the size of the file
    Given I am on the "bridge" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should see the flash message "Variation files have been generated successfully."
    And I should see the text "KB"

  @jira https://akeneo.atlassian.net/browse/PIM-7108
  Scenario: The asset last update should change when a new reference file is uploaded
    Given I am on the "dog" asset page
    And the "dog" asset already has an update date
    When I upload the reference file akene.jpg
    And I save the asset
    Then I should not see the text "There are unsaved changes."
    And the new update date of the asset "dog" should be more recent than the previous one
