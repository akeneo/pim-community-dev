@javascript
Feature: List assets categories
  In order to be able to see the categories in my catalog
  As a product manager
  I need to be able to list existing categories

  Background:
    Given a "clothing" catalog configuration

  Scenario: Successfully display assets categories
    Given I am logged in as "Julia"
    When I am on the assets categories page
    Then I should see "Asset main catalog"
    And I should see "Images"
    And I should see "Print"
    And I should see "Videos"
    And I should see "Client documents"
    And I should see "Store documents"
    And I should see "Technical documents"
    And I should see "Sales documents"
    And I should see "Archives"
    And I should see "Please select a category on the left or Create a new category"

  Scenario: Click on a asset category without the right permissions do nothing
    Given I am logged in as "Peter"
    When I am on the "Administrator" role page
    And I remove rights to Edit an asset category
    And I remove rights to Create an asset category
    And I save the role
    And I wait 5 seconds
    Given I am on the assets categories page
    Then I should not see "Please select a category on the left or Create a new category"
    When I click on the "Asset main catalog" category
    Then I should not see "Server error"
    Then I reset the "Administrator" rights
