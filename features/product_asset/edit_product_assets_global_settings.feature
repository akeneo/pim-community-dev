@javascript
Feature: Edit product assets global settings
  In order to enrich the existing product assets
  As a product manager
  I need to be able to edit product assets global settings

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the assets page

  Scenario: Successfully update value to global settings of an asset
    When I am on the "bridge" asset page
    And I visit the "Global settings" tab
    When I change the Tags to "flower, vintage, solid_color"
    And I change the Description to "My new description"
    And I change the "End of use at" to "2001-01-01"
    And I save the asset
    Then the asset "bridge" should have the following values:
      | description | My new description         |
      | tags        | flower,vintage,solid_color |
      | endOfUseAt  | 2001-01-01                 |
