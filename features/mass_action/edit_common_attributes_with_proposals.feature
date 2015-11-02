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
    And the following category:
      | code | label-en_US | parent          |
      | hat  | Hat         | 2014_collection |
    And the following product category accesses:
      | product category | user group | access |
      | hat              | Redactor   | own    |
    And the following products:
      | sku          | categories | family     |
      | owned        | hat        | high_heels |
      | editable     | tees       | high_heels |
      | viewable     | pants      | high_heels |
      | notviewable  | jeans      | high_heels |
      | unclassified |            | high_heels |
    And I am logged in as "Mary"
    And I am on the products page

  @jira https://akeneo.atlassian.net/browse/PIM-3980 https://akeneo.atlassian.net/browse/PIM-4775
  Scenario: Successfully creates proposal on editable products
    Given I mass-edit products viewable, editable and owned
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I display the Name attribute
    And I change the "Name" to "My awesome name"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    And I should see "Proposal created 1"
    And I should see "skipped products 1"
    And I should see "processed 1"
    Then I logout
    And I am logged in as "admin"
    And I edit the "viewable" product
    And I visit the "Proposals" tab
    Then I should not see "My awesome name"
    And I edit the "editable" product
    And I visit the "Proposals" tab
    Then I should see "My awesome name"
    And I edit the "owned" product
    And I visit the "Proposals" tab
    Then I should not see "My awesome name"
