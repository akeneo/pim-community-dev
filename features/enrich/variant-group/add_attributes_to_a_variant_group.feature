@javascript
Feature: Add attributes to a variant group
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to add attributes to a variant group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Display available attributes for a variant group
    Given the following attribute:
      | code   | label-en_US | unique | group     |
      | unique | Unique      | yes    | marketing |
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    Then I should see available attribute Name in group "Product information"
    And I should see available attribute Price in group "Marketing"
    And I should see available attribute Comment in group "Other"
    But I should not see available attribute Size in group "Sizes"
    And I should not see available attribute Color in group "Colors"
    And I should not see available attribute SKU in group "Product information"
    And I should not see available attribute Unique in group "Marketing"

  Scenario: Add some available attributes to a variant group
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    When I add available attributes Name, Price and Comment
    And I visit the "Product information" group
    Then I should see the Name field
    When I visit the "Marketing" group
    Then I should see the Price field
    When I visit the "Other" group
    Then I should see the Comment field
    And I should not see available attribute Name in group "Product information"
    And I should not see available attribute Price in group "Marketing"

  Scenario: Update values of products in a variant group only after saving the group (not immediately after adding a new attribute)
    Given the following product:
      | sku  | groups            | name-en_US | color | size |
      | boot | caterpillar_boots | foo        | black | 39   |
    And I am on the "caterpillar_boots" variant group page
    Then the english Name of "boot" should be "foo"
    When I visit the "Attributes" tab
    And I add available attribute Name
    Then the english Name of "boot" should be "foo"
    When I save the variant group
    And I should see the flash message "Variant group successfully updated"
    Then the english Name of "boot" should be ""

  Scenario: Update products when values are changed on the variant group page
    Given the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 39   |
    And I am on the "caterpillar_boots" variant group page
    When I visit the "Attributes" tab
    And I add available attribute Name
    And I fill in the following information:
     | Name | bar |
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    Then the english Name of "boot" should be "bar"

  Scenario: Remove an attribute which is linked to a variant group
    Given the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 39   |
    And I am on the "caterpillar_boots" variant group page
    When I visit the "Attributes" tab
    And I add available attribute Name
    And I add available attribute Description
    Then I am on the attributes page
    When I filter by "Label" with value "Name"
    And I click on the "Delete" action of the row which contains "Name"
    And I confirm the deletion
    Then I am on the "caterpillar_boots" variant group page
    And I should not see available attribute Name in group "Product information"

  Scenario: The price attribute should be visible once added
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    When I add available attributes Price
    And I should see "EUR, USD" currencies on the Price price field

  @jira https://akeneo.atlassian.net/browse/PIM-5208
  Scenario: View only attribute filters that are usable as grid filters and view varient axes in columns
    Given the following attributes:
      | code                       | label-en_US                | type         | group  | useable_as_grid_filter |
      | high_heel_main_color       | High heel main color       | simpleselect | colors | yes                    |
      | high_heel_main_fabric      | High heel main fabric      | simpleselect | other  | no                     |
      | high_heel_secondary_color  | High heel secondary color  | simpleselect | colors | no                     |
      | high_heel_secondary_fabric | High heel secondary fabric | simpleselect | other  | yes                    |
    And the following "high_heel_main_color" attribute options: Red, Blue
    And the following "high_heel_main_fabric" attribute options: Leather, Silk
    And the following family:
      | code       | requirements-mobile                              | requirements-tablet |
      | high_heels | sku, high_heel_main_color, high_heel_main_fabric | sku                 |
    And the following product groups:
      | code       | label      | axis                                        | type    |
      | high_heels | High heels | high_heel_main_color, high_heel_main_fabric | VARIANT |
    And the following product:
      | sku     | family     | high_heel_main_color | high_heel_main_fabric |
      | heel001 | high_heels | Red                  | Silk                  |
    When I am on the "high_heels" variant group page
    Then I should see the available filters High heel main color and High heel secondary fabric
    And I should not see the available filters High heel main fabric and High heel secondary color
    And I should see the columns In group, Sku, High heel main color, High heel main fabric, Label, Family, Status, Complete, Created at and Updated at

