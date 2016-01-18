@javascript
Feature: Filter products by reference data with locale and scope
  In order to filter products in the catalog per reference data
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "footwear" catalog configuration
    And the following "cap_color" attribute reference data: Black, Purple and Orange
    And the following "lace_fabric" attribute reference data: Cotton, Flax and Straw
    And the following products:
      | sku    |
      | postit |
      | mug    |
    And the following product values:
      | product | attribute   | value       | scope  | locale |
      | postit  | cap_color   | Black       | tablet | en_US  |
      | postit  | cap_color   | Purple      | mobile | en_US  |
      | postit  | lace_fabric | Cotton,Flax | tablet | en_US  |
      | postit  | lace_fabric | Straw       | mobile | en_US  |
    And I am logged in as "Mary"
    And I am on the products page

  Scenario: Successfully filter products by reference data
    Given I should not see the filter cap_color
    And the grid should contain 2 elements
    And I should be able to use the following filters:
      | filter      | value        | result |
      | Cap color   | black        | postit |
      | Cap color   | black,orange | postit |
      | Cap color   | is empty     | mug    |
      | Cap color   | orange       |        |
      | Lace fabric | cotton       | postit |
      | Lace fabric | cotton,straw | postit |
      | Lace fabric | flax         | postit |
      | Lace fabric | straw        |        |
      | Lace fabric | is empty     | mug    |
    When I filter by "Channel" with value "Mobile"
    Then I should be able to use the following filters:
      | filter      | value         | result |
      | Cap color   | purple        | postit |
      | Cap color   | purple,orange | postit |
      | Cap color   | is empty      | mug    |
      | Cap color   | orange        |        |
      | Lace fabric | straw         | postit |
      | Lace fabric | cotton,straw  | postit |
      | Lace fabric | flax          |        |
      | Lace fabric | cotton        |        |
      | Lace fabric | is empty      | mug    |

  Scenario: Successfully filter product with multi reference data filters
    Given I show the filter "Cap color"
    And I filter by "Cap color" with value "Black"
    And I should be able to use the following filters:
      | filter      | value        | result |
      | Lace fabric | Cotton       | postit |
      | Lace fabric | Flax         | postit |
      | Lace fabric | Cotton,Straw | postit |
      | Lace fabric | Cotton,Flax  | postit |
      | Lace fabric | Straw        |        |
      | Lace fabric | is empty     |        |
    When I filter by "Channel" with value "Mobile"
    And I hide the filter "Cap color"
    And I show the filter "Cap color"
    And I filter by "Cap color" with value "Purple"
    Then I should be able to use the following filters:
      | filter      | value        | result |
      | Lace fabric | Straw        | postit |
      | Lace fabric | Flax         |        |
      | Lace fabric | Cotton,Straw | postit |
      | Lace fabric | Cotton,Flax  |        |
      | Lace fabric | Cotton       |        |
      | Lace fabric | is empty     |        |
