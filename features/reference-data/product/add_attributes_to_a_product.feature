Feature: Add reference data attributes to a product
  In order to provide more information about a product
  As a product manager
  I need to be able to add attributes to a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
    | sku     | family  |
    | boots   | boots   |
    And I am logged in as "Julia"

  @javascript
  Scenario: Display available reference data attributes to a product
    Given I am on the "boots" product page
    Then I should see available attribute Heel color in group "Other"
    Then I should see available attribute Sole color in group "Other"
    Then I should see available attribute Sole fabric in group "Other"
    Then I should see available attribute Lace fabric in group "Other"

  @javascript
  Scenario: Add some available reference data attributes to a product
    Given I am on the "boots" product page
    When I add available attributes Sole color and Sole fabric
    Then attributes in group "Other" should be Sole color and Sole fabric
    And I should see available attribute Number in stock in group "Other"
    And I should not see available attribute Sole color in group "Other"
    And I should not see available attribute Sole fabric in group "Other"
