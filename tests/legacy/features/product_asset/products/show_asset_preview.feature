@javascript
Feature: Show asset collection preview
  In order to show the asset collection preview
  As an asset manager
  I need to be able to use the asset collection preview in product edit form

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku   | family |
      | shirt | tees   |
    And I am logged in as "Pamela"
    And I am on the "shirt" product page
    And I visit the "Media" group
    And I start to manage assets for "Front view"
    And I check the rows "akene, autumn, chicagoskyline"
    And I confirm the asset modification
    And I save the product

  Scenario: Successfully show the asset collection preview
    When I click on the "chicagoskyline" asset thumbnail
    Then I should see the text "Asset collection preview"
    And I should see the text "This is chicago!"

  Scenario: Successfully navigate in the asset collection preview
    Given I click on the "chicagoskyline" asset thumbnail
    When I navigate to the right in the asset collection preview
    Then I should see the text "Because Akeneo"
    When I navigate to the right in the asset collection preview
    Then I should see the text "Leaves and water"
    When I navigate to the right in the asset collection preview
    Then I should see the text "This is chicago!"
    When I navigate to the left in the asset collection preview
    Then I should see the text "Leaves and water"

  Scenario: Successfully delete item in the asset collection preview
    Given I click on the "chicagoskyline" asset thumbnail
    When I press the "Remove" button
    And I press the "Yes" button
    And I close the asset collection preview
    Then I should not see the text "This is chicago!"
