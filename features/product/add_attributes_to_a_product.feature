@javascript
Feature: Add attributes to a product
  In order to provide more information about a product
  As a product manager
  I need to be able to add attributes to a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
    | sku     | family  |
    | sandals | sandals |
    | boots   |         |
    And I am logged in as "Julia"

  Scenario: Display available attributes to a product
    Given I am on the "sandals" product page
    Then I should see available attribute Weather conditions in group "Product information"
    And I should see available attribute Lace color in group "Colors"
    And I should see available attribute Top view in group "Media"
    But I should not see available attribute Side view in group "Media"

  Scenario: Add some available attributes to a product
    Given I am on the "sandals" product page
    When I add available attributes Weather conditions and Lace color
    Then attributes in group "Colors" should be Color and Lace color
    And I should see available attribute Top view in group "Media"
    And I should not see available attribute Lace color in group "Colors"
    And I should not see available attribute Weather conditions in group "Product information"

  Scenario: Successfully add a metric attribute to a product
    Given I am on the "boots" product page
    And I add available attribute Length
    Then I should see the text "Length"
    And I should see the text "Centimeter"
    When I change the Length to "29 Centimeter"
    And I save the product
    Then the product Length should be "29 Centimeter"

  Scenario: Successfully display unclassified attributes in group "Other"
    Given I am on the "sandals" product page
    Then I should see available attribute Comment in group "Other"

  Scenario: Successfully add metric attribute with "0" value
    Given I am on the "boots" product page
    And I add available attribute Rate of sale
    Then attributes in group "Marketing" should be Rate of sale
    When I change the "Rate of sale" to "0"
    And I save the product
    Then the product Rate of sale should be "0"

