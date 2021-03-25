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
      | product     | status      | source | source_label | author | author_label  | result                                                                    |
      | black-boots | in progress | pim    | PIM          | Julia  | Julia Stark   | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
      | black-boots | ready       | pim    | PIM          | Mary   | Mary Smith    | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change2"}]}} |
      | white-boots | ready       | pim    | PIM          | Sandra | Sandra Harvey | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change3"}]}} |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-3980

  Scenario: Successfully display product drafts
    Given I edit the "black-boots" product
    When I visit the "Proposals" column tab
    Then the grid should contain 2 elements
    And I should see the column Proposed at
    And I should see entities Julia and Mary
