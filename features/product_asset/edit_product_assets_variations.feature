@javascript
Feature: Edit product assets variations
  In order to enrich the existing product assets
  As a product manager
  I need to be able to edit product assets variations

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the assets page

  Scenario: Successfully delete reference file
    When I am on the "bridge" asset page
    And I visit the "Variations" tab
    And I delete the reference file
    And I confirm the deletion
    And I wait 50 seconds

