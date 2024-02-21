@javascript
Feature: List attribute groups
  In order to see attribute groups in my catalog
  As a product manager
  I need to be able to list existing attribute groups

  Scenario: Successfully display attribute groups
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute groups page
    Then I should see the text "Product information"
    Then I should see the text "Marketing"
