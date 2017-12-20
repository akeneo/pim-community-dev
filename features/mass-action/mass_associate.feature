@javascript
Feature: Associate many products at once
  In order to easily associate products to other products
  As a product manager
  I need to associate many products to others at once with a form

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Mass associate products to products
    When I sort by "ID" value ascending
    Given I select rows Bag, Belt and Hat
    And I press the "Bulk actions" button
    And I choose the "Associate to products" operation
    And I move on to the choose step
    And I choose the "Associate to products" operation
    Given I press the "Add associations" button and wait for modal
    And I check the row "Scarf"
    And the item picker basket should contain Scarf
    And I check the row "Sunglasses"
    And the item picker basket should contain Sunglasses
    And I press the "Confirm" button in the popin
    And I should see the text "Scarf"
    And I should see the text "Sunglasses"
    And I validate mass edit
    And I wait for the "add_association" job to finish
    Then the product "1111111171" should have the following associations:
      | type   | products              |
      | X_SELL | 1111111292,1111111304 |
    Then the product "1111111172" should have the following associations:
      | type   | products              |
      | X_SELL | 1111111292,1111111304 |
    Then the product "1111111240" should have the following associations:
      | type   | products              |
      | X_SELL | 1111111292,1111111304 |

  Scenario: Mass associate products to product models
    When I sort by "ID" value ascending
    Given I select rows Bag, Belt and Hat
    And I press the "Bulk actions" button
    And I choose the "Associate to products" operation
    And I move on to the choose step
    And I choose the "Associate to products" operation
    Given I press the "Add associations" button and wait for modal
    And I search "juno"
    And I check the row "juno"
    And the item picker basket should contain juno
    And I search "amor"
    And I check the row "amor"
    And the item picker basket should contain amor
    And I press the "Confirm" button in the popin
    And I should see the text "juno"
    And I should see the text "amor"
    And I validate mass edit
    And I wait for the "add_association" job to finish
    Then the product "1111111171" should have the following associations:
      | type   | product_models |
      | X_SELL | amor,juno      |
    Then the product "1111111172" should have the following associations:
      | type   | product_models |
      | X_SELL | amor,juno      |
    Then the product "1111111240" should have the following associations:
      | type   | product_models |
      | X_SELL | amor,juno      |

  Scenario: Mass associate product model children to products
    When I sort by "ID" value ascending
    Given I select rows amor
    And I press the "Bulk actions" button
    And I choose the "Associate to products" operation
    And I move on to the choose step
    And I choose the "Associate to products" operation
    Given I press the "Add associations" button and wait for modal
    And I check the row "Scarf"
    And the item picker basket should contain Scarf
    And I check the row "Sunglasses"
    And the item picker basket should contain Sunglasses
    And I press the "Confirm" button in the popin
    And I should see the text "Scarf"
    And I should see the text "Sunglasses"
    And I validate mass edit
    And I wait for the "add_association" job to finish
    Then the product "1111111113" should have the following associations:
      | type   | products              |
      | X_SELL | 1111111292,1111111304 |
    Then the product "1111111112" should have the following associations:
      | type   | products              |
      | X_SELL | 1111111292,1111111304 |
    Then the product "1111111111" should have the following associations:
      | type   | products              |
      | X_SELL | 1111111292,1111111304 |
