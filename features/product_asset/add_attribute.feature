@javascript
Feature: Add assets collection
  In order to create a collection of assets
  As a product manager

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully create a collection of assets
    Given I create an "Assets collection" attribute
    Given I fill in the following information:
      | Code                | blue_tshirt |
      | Attribute group     | Other       |
    Then I save the attribute
    Then I should see flash message "Attribute successfully created"
