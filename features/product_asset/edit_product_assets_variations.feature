@javascript
Feature: Edit product assets variations
  In order to enrich the existing product assets
  As a product manager
  I need to be able to edit product assets variations

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully delete reference file
    Given I am on the assets page
    When I am on the "bridge" asset page
    And I visit the "Variations" tab
    Then I can upload a Mobile variation file
    And I can upload a Tablet variation file
    And I should be able to generate Mobile from reference
    And I should be able to generate Tablet from reference

    Given I delete the reference file
    And I confirm the deletion
    Then I can upload a reference file
    And I can upload a Mobile variation file
    And I can upload a Tablet variation file
    And I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference

  @skip
  Scenario: Successfully delete variation file
    Given I am on the assets page
    When I am on the "bridge" asset page
    And I visit the "Variations" tab
