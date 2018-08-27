@javascript
Feature: Revert a product model to a previous version
  In order to manage versioning for product models
  As a product manager
  I need to be able to revert a product model to a previous version

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  @todo PIM-6907: Revert will be possible eventually
  Scenario: Do not display restore button in product model history panel
    Given I am on the "1111111111" product page
    When I visit the "History" column tab
    Then I should see the text "RESTORE"
    When I navigate to the selected element for level 0
    And I visit the "History" column tab
    Then I should not see the text "RESTORE"
