@javascript
Feature: Sort products
  In order to enrich my catalog
  As a regular user
  I need to be able to manually sort products per reference data attributes

  Scenario: Successfully sort products by simple reference data
    Given the "footwear" catalog configuration
    And the following "sole_color" attribute reference data: Red, Blue and Green
    And the following "sole_fabric" attribute reference data: Cashmerewool, Neoprene and Silk
    And the following "heel_color" attribute reference data: Pink, Purple and Black
    And the following products:
      | sku    |
      | postit |
      | mug    |
    And the following product values:
      | product | attribute   | value             |
      | postit  | sole_color  | Red               |
      | postit  | heel_color  | Pink              |
      | postit  | sole_fabric | Cashmerewool,Silk |
    And I am logged in as "Mary"
    When I am on the products page
    Then the grid should contain 2 elements
    When I display the columns SKU, Sole color, Heel color and Sole fabric
    Then I sort by "Sole color" value ascending

  @jira https://akeneo.atlassian.net/browse/PIM-7041
  Scenario: Sort a simple-select reference data attribute with the same name than its reference data
    Given the "default" catalog configuration
    And the following attributes:
      | code  |  label-en_US  | type                            | reference_data_name | useable_as_grid_filter | group |
      | color |  Color        | pim_reference_data_simpleselect | color               | yes                    | other |
    And the following "color" attribute reference data: Red, Blue and Green
    And the following family:
      | code     | requirements-mobile | requirements-ecommerce |
      | whatever | sku                 | sku                    |
    And the following products:
      | sku      | family   |
      | productA | whatever |
      | productB | whatever |
    And the following product values:
      | product  | attribute | value |
      | productA | color     | Red   |
    And I am logged in as "Mary"
    When I am on the products page
    And I display the columns SKU and color
    Then I should see the text "Red"
    When I sort by "Color" value ascending
    Then I should see the text "Red"
