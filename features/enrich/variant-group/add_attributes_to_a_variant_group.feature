@javascript
Feature: Add attributes to a variant group
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to add attributes to a variant group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @skip @info Will be removed in PIM-6444
  Scenario: Display available attributes for a variant group
    Given the following attribute:
      | code   | label-en_US | unique | group     | type             |
      | unique | Unique      | 1      | marketing | pim_catalog_text |
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    Then I should see available attribute Name in group "Product information"
    And I should see available attribute Price in group "Marketing"
    And I should see available attribute Comment in group "Other"
    But I should not see available attribute Size in group "Sizes"
    And I should not see available attribute Color in group "Colors"
    And I should not see available attribute SKU in group "Product information"
    And I should not see available attribute Unique in group "Marketing"

  @skip @info Will be removed in PIM-6444
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

  @skip @info Will be removed in PIM-6444
  Scenario: Update values of products in a variant group only after saving the group (not immediately after adding a new attribute)
    Given the following product:
      | sku  | groups            | name-en_US | color | size |
      | boot | caterpillar_boots | foo        | black | 39   |
    And I am on the "caterpillar_boots" variant group page
    Then the english localizable value Name of "boot" should be "foo"
    When I visit the "Attributes" tab
    And I add available attribute Name
    When I save the variant group
    And I should see the flash message "Variant group successfully updated"
    When I am on the "boot" product page
    Then the field Name should contain ""

  @skip @info Will be removed in PIM-6444
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
    When I am on the "boot" product page
    Then the field Name should contain "bar"

  @skip @info Will be removed in PIM-6444
  Scenario: Remove an attribute which is linked to a variant group
    Given the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 39   |
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Name, Description
    And I save the variant group
    And I am on the attributes page
    And I search "Name"
    And I collapse the column
    And I click on the "Delete" action of the row which contains "Name"
    And I confirm the deletion
    When I am on the "caterpillar_boots" variant group page
    Then I should not see available attribute Name in group "Product information"

  @skip @info Will be removed in PIM-6444
  Scenario: The price attribute should be visible once added
    Given I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    When I add available attributes Price
    And I should see "EUR, USD" currencies on the Price price field

  @skip @info Will be removed in PIM-6444 @jira https://akeneo.atlassian.net/browse/PIM-5208
  Scenario: View only attribute filters that are usable as grid filters and view variant axes in columns
    Given the following attributes:
      | code                       | label-en_US                | type                     | group  | useable_as_grid_filter |
      | high_heel_main_color       | High heel main color       | pim_catalog_simpleselect | colors | 1                      |
      | high_heel_main_fabric      | High heel main fabric      | pim_catalog_simpleselect | other  | 0                      |
      | high_heel_secondary_color  | High heel secondary color  | pim_catalog_simpleselect | colors | 0                      |
      | high_heel_secondary_fabric | High heel secondary fabric | pim_catalog_simpleselect | other  | 1                      |
    And the following "high_heel_main_color" attribute options: Red, Blue
    And the following "high_heel_main_fabric" attribute options: Leather, Silk
    And the following family:
      | code       | requirements-mobile                            | requirements-tablet | attributes                                     |
      | high_heels | sku,high_heel_main_color,high_heel_main_fabric | sku                 | sku,high_heel_main_color,high_heel_main_fabric |
    And the following variant groups:
      | code       | label-en_US | axis                                       | type    |
      | high_heels | High heels  | high_heel_main_color,high_heel_main_fabric | VARIANT |
    And the following product:
      | sku     | family     | high_heel_main_color | high_heel_main_fabric |
      | heel001 | high_heels | Red                  | Silk                  |
    When I am on the "high_heels" variant group page
    And I collapse the column
    Then I should see the available filters high_heel_main_color and high_heel_secondary_fabric
    And I should not see the available filters High heel main fabric and High heel secondary color
    And I should see the columns In group, Sku, High heel main color, High heel main fabric, Label, Family, Status, Complete, Created at and Updated at
