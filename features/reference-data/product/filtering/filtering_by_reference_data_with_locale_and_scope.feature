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
    And I am on the products grid

  Scenario: Successfully filter products by reference data
    Given I should not see the filter cap_color
    And the grid should contain 2 elements
    And I should be able to use the following filters:
      | filter      | operator     | value        | result |
      | cap_color   | in list      | Black        | postit |
      | cap_color   | in list      | Black,Orange | postit |
      | cap_color   | is empty     |              | mug    |
      | cap_color   | is not empty |              | postit |
      | cap_color   | in list      | Orange       |        |
      | lace_fabric | in list      | Cotton       | postit |
      | lace_fabric | in list      | Cotton,Straw | postit |
      | lace_fabric | in list      | Flax         | postit |
      | lace_fabric | in list      | Straw        |        |
      | lace_fabric | is empty     |              | mug    |
      | lace_fabric | is not empty |              | postit |
    When I switch the scope to "Mobile"
    Then I should be able to use the following filters:
      | filter      | operator     | value         | result |
      | cap_color   | in list      | Purple        | postit |
      | cap_color   | in list      | Purple,Orange | postit |
      | cap_color   | is empty     |               | mug    |
      | cap_color   | is not empty |               | postit |
      | cap_color   | in list      | Orange        |        |
      | lace_fabric | in list      | Straw         | postit |
      | lace_fabric | in list      | Cotton,Straw  | postit |
      | lace_fabric | in list      | Flax          |        |
      | lace_fabric | in list      | Cotton        |        |
      | lace_fabric | is empty     |               | mug    |
      | lace_fabric | is not empty |               | postit |

  Scenario: Successfully filter product with multi reference data filters
    Given I show the filter "cap_color"
    And I filter by "cap_color" with operator "in list" and value "Black"
    And I should be able to use the following filters:
      | filter      | operator     | value        | result |
      | lace_fabric | in list      | Cotton       | postit |
      | lace_fabric | in list      | Flax         | postit |
      | lace_fabric | in list      | Cotton,Straw | postit |
      | lace_fabric | in list      | Cotton,Flax  | postit |
      | lace_fabric | in list      | Straw        |        |
      | lace_fabric | is empty     |              |        |
      | lace_fabric | is not empty |              | postit |
    When I switch the scope to "Mobile"
    And I hide the filter "cap_color"
    And I show the filter "cap_color"
    And I filter by "cap_color" with operator "in list" and value "Purple"
    Then I should be able to use the following filters:
      | filter      | operator     | value        | result |
      | lace_fabric | in list      | Straw        | postit |
      | lace_fabric | in list      | Flax         |        |
      | lace_fabric | in list      | Cotton,Straw | postit |
      | lace_fabric | in list      | Cotton,Flax  |        |
      | lace_fabric | in list      | Cotton       |        |
      | lace_fabric | is empty     |              |        |
      | lace_fabric | is not empty |              | postit |
