@javascript
Feature: Edit product assets global settings
  In order to enrich the existing product assets
  As a product manager
  I need to be able to edit product assets global settings

  Background:
    Given the "clothing" catalog configuration
    And the following assets:
      | code       | tags             | description       | end of use at | enabled |
      | blue_shirt | solid_color, men | A beautiful shirt | now           | yes     |
    And I am logged in as "Julia"

  Scenario: Successfully edit the description of an asset
    Given I am on the "blue_shirt" asset page
    And I visit the "Global settings" tab
    When I fill in the following information:
      | Description | My new description |
    And I press the "Save" button
    Then I should be on the "blue_shirt" asset edit page
    And I visit the "Global settings" tab
    And the field Description should contain "My new description"

  Scenario: Successfully edit the description of an asset and back to grid
    Given I am on the "blue_shirt" asset page
    And I visit the "Global settings" tab
    When I fill in the following information:
      | Description | My new description |
    And I press "Save and back to grid" on the "Save" dropdown button
    Then I should be on the assets page
    And I should see assets blue_shirt
    And the row "blue_shirt" should contain:
      | column      | value              |
      | code        | blue_shirt         |
      | description | My new description |

  Scenario: Successfully add tags to an asset
    Given I am on the "blue_shirt" asset page
    And I visit the "Global settings" tab
    When I add the following tags in the "Tags" select2 : pattern, stripes, neckline
    And I press the "Save" button
    Then I should be on the "blue_shirt" asset edit page
    And I visit the "Global settings" tab
    And the field Tags should contain "solid_color, men, pattern, stripes, neckline"

  Scenario: Successfully remove tags from an asset
    Given I am on the "blue_shirt" asset page
    And I visit the "Global settings" tab
    When I remove the following tags from the "Tags" select2 : solid_color
    And I press the "Save" button
    Then I should be on the "blue_shirt" asset edit page
    And I visit the "Global settings" tab
    And the field Tags should contain "men"

  Scenario: Successfully edit the end of use at of an asset
    Given I am on the "blue_shirt" asset page
    And I visit the "Global settings" tab
    When I change the end of use at to "2050-06-20"
    And I press the "Save" button
    Then I should be on the "blue_shirt" asset edit page
    And I visit the "Global settings" tab
    And the field End of use at should contain "2050-06-20"
