@javascript
Feature: Browse product drafts for a specific product
  In order to list the existing product drafts for a specific product
  As a product manager
  I need to be able to see product drafts

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family |
      | black-boots | boots  |
      | white-boots | boots  |
    And the following product drafts:
      | product     | status      | author | result                                                                    |
      | black-boots | in progress | Julia  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
      | black-boots | ready       | Mary   | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change2"}]}} |
      | white-boots | ready       | Sandra | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change3"}]}} |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully display product drafts
    Given I edit the "black-boots" product
    When I visit the "Proposals" tab
    Then the grid should contain 2 elements
    And I should see the columns Author, Changes, Proposed at and Status
    And I should see entities Julia and Mary
