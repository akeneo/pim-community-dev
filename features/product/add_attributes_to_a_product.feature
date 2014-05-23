@javascript
Feature: Add attributes to a product
  In order to provide more information about a product restricting accesses
  As a user
  I need to be able to add only attributes I have access to a product

  Background:
    Given a "footwear" catalog configuration
    And the following attribute group accesses:
      | group     | role          | access |
      | info      | Administrator | edit   |
      | marketing | Administrator | view   |
    And the following products:
      | sku     | family  |
      | sandals | sandals |
    And I am logged in as "Peter"

  Scenario: Successfully display only attributes I have edit rights access
    Given I am on the "sandals" product page
    Then I should see available attribute Weather conditions and Length in group "Product information"
    And I should not see available attribute Rating and Price in group "Marketing"
    And I should not see available attribute Lace color in group "Colors"
    And I should not see available attribute Top view in group "Media"
