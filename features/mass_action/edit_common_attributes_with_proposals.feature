@javascript
Feature: Apply permissions for an attribute group when mass edit common attributes
  In order to be able to only edit the product data I have access
  As a product manager
  I need to be able to create proposals on product I don't own

  Background:
    Given the "clothing" catalog configuration
    And the following family:
      | code       | attributes |
      | high_heels | sku, name  |
    And the following products:
      | sku          | categories | family     |
      | owned        | tops       | high_heels |
      | viewable1    | pants      | high_heels |
      | viewable2    | pants      | high_heels |
      | notviewable  | jeans      | high_heels |
      | unclassified |            | high_heels |
    And I am logged in as "Mary"
    And I am on the products page

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully creates proposal on products I don't own
    Given I mass-edit products viewable1, viewable2 and owned
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I display the Name attribute
    And I change the "Name" to "My awesome name"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    And I should see "Proposal created 2"
    Then I logout
    And I am logged in as "Julia"
    And I edit the "viewable1" product
    And I visit the "Proposals" tab
    Then I should see "My awesome name"
    And I edit the "viewable2" product
    And I visit the "Proposals" tab
    Then I should see "My awesome name"
    And I edit the "owned" product
    And I visit the "Proposals" tab
    Then I should not see "My awesome name"
