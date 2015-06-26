@javascript
Feature: Display the product history
  In order to know who, when and what changes has been made to a product
  As a product manager
  I need to have access to a product history

  Scenario: Display product updates and published version
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku         | family  |
      | sandals-001 | sandals |
    And I edit the "sandals-001" product
    And I fill in the following information:
      | Name | BG sandals |
    And I save the product
    And I press the "Publish" button
    And I confirm the publishing
    And I edit the "sandals-001" product
    And I fill in the following information:
      | SKU | sandals-001-bis |
    And I save the product
    When I open the history
    Then there should be 3 update
    And I should see history:
      | version | property   | value           |
      | 1       | SKU        | sandals-001     |
      | 2       | Name en    | BG sandals      |
      | 3       | SKU        | sandals-001-bis |
    And the version 2 should be marked as published
