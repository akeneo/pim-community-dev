@javascript
Feature: Delete attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to delete options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                         | localizable | scopable | group | decimals_allowed |
      | color       | Color       | pim_catalog_simpleselect     | 0           | 0        | other |                  |
      | description | Description | pim_catalog_textarea         | 1           | 1        | other |                  |
      | name        | Name        | pim_catalog_text             | 1           | 0        | other |                  |
      | size        | Size        | pim_catalog_simpleselect     | 0           | 0        | other |                  |
    And the following "color" attribute options: red, yellow, black and white
    And the following "size" attribute options: s, m, l, xl
    And the following family:
      | code | requirements-ecommerce | requirements-mobile | attributes                      |
      | bags | sku                    | sku                 | color,description,name,size,sku |
    And the following family variants:
      | code        | family | variant-axes_1 | variant-attributes_1       |
      | bag_colored | bags   | color          | size,color,description,sku |
    And the following root product models:
      | code       | categories | family_variant | name-en_US  |
      | bag_atelle | default    | bag_colored    | Bag atelle  |
    And the following products:
      | sku              | parent     | family | categories | color |
      | bag_atelle_white | bag_atelle | bags   | default    | white |
      | lonely_bag       |            | bags   | default    | red   |

  Scenario: Successfully delete attribute options if it is not used as variant axis
    Given I am logged in as "Julia"
    And I am on the "color" attribute page
    And I visit the "Options" tab
    When I remove the "red" option
    And I confirm the deletion
    Then I should not see the text "Error during deletion of the attribute option"
    Then I should not see the text "red"

  Scenario: Fail to remove an attribute option if it is used as variant axis
    Given I am logged in as "Julia"
    And I am on the "color" attribute page
    And I visit the "Options" tab
    When I remove the "white" option
    And I confirm the deletion
    And I should see the text "Attribute option \"white\" could not be removed as it is used as variant axis value."
    Then I should see the text "white"
