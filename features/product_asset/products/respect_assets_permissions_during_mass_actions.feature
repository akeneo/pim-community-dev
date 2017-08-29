@javascript
Feature: Apply permissions for an assets collection attribute during mass action operations
  In order to be able to only edit the product data I have access
  As a product manager
  I need to be able to mass edit only attributes I have access

  Background:
    Given a "clothing" catalog configuration
    And the following assets:
      | code     | categories          |
      | doc_tech | technical_documents |
    And the following products:
      | sku            | family  | front_view |
      | leather jacket | jackets | paint      |
      | wool jacket    | jackets | akene      |
    And I am logged in as "Julia"

  Scenario: Apply permissions for an assets collection attribute during editing common attributes
    Given I am on the products grid
    And I select rows leather jacket and wool jacket
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    When I display the Front view attribute
    And I start to manage assets for "Front view"
    Then I should see entities paint and dog
    But I should not see entities doc_tech
