@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code       | attributes                                                       |
      | high_heels | sku, name, description, price, rating, size, color, manufacturer |
    And the following attributes:
      | code        | label       | type   | metric family | default metric unit | families                 |
      | weight      | Weight      | metric | Weight        | GRAM                | boots, sneakers, sandals |
      | heel_height | Heel Height | metric | Length        | CENTIMETER          | high_heels, sandals      |
    And the following product groups:
      | code          | label         | axis  | type    |
      | variant_heels | Variant Heels | color | VARIANT |
    And the following variant group values:
      | group         | attribute   | value         |
      | variant_heels | heel_height | 12 CENTIMETER |
    And the following products:
      | sku            | family     | color | groups        |
      | boots          | boots      |       |               |
      | sneakers       | sneakers   |       |               |
      | sandals        | sandals    |       |               |
      | pump           |            |       |               |
      | highheels      | high_heels | red   | variant_heels |
      | blue_highheels | high_heels | blue  | variant_heels |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Allow editing all attributes on configuration screen
    Given I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Side view in group "Media"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I should see available attribute Weight in group "Other"

  Scenario: Successfully update many text values at once
    Given I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the english name of "boots" should be "boots"
    And the english name of "sandals" should be "boots"
    And the english name of "sneakers" should be "boots"

  Scenario: Successfully update many multi-valued values at once
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |

  @info https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Successfully mass edit product values that does not belong yet to the product
    Given I set product "pump" family to "sneakers"
    When I mass-edit products pump and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the english name of "pump" should be "boots"
    And the english name of "sneakers" should be "boots"

  @info https://akeneo.atlassian.net/browse/PIM-3070
  Scenario: Successfully mass edit a price not added to the product
    Given I create a new product
    And I fill in the following information in the popin:
      | SKU             | Shoes |
      | Choose a family | Heels |
    And I press the "Save" button in the popin
    Then I should be on the product "Shoes" edit page
    And I am on the products page
    When I mass-edit products Shoes
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I change the "Price" to "100 USD"
    And I change the "Price" to "150 EUR"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the prices "Price" of products Shoes should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  @jira https://akeneo.atlassian.net/browse/PIM-3426
  Scenario: Successfully update multi-valued value at once where the product have already one of the value
    Given the following product values:
      | product | attribute          | value   |
      | boots   | weather_conditions | dry,hot |
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |

  @jira https://akeneo.atlassian.net/browse/PIM-4528
  Scenario: See previously selected fields on mass edit error
    Given I mass-edit products boots and sandals
    And I choose the "Edit common attributes" operation
    And I display the Weight and Name attribute
    Then I visit the "Other" group
    And I change the "Weight" to "Edith"
    And I move on to the next step
    Then I should see the text "Product information"
    And I should see the text "Weight"
    Then I visit the "Product information" group
    And I should see the text "Name"
    When I am on the attributes page
    And I am on the products page
    And I mass-edit products boots and sandals
    And I choose the "Edit common attributes" operation
    Then I should not see the text "Product information"
    And I should not see the text "Weight"
    And I should not see the text "Name"

  @jira https://akeneo.atlassian.net/browse/PIM-4777
  Scenario: Doing a mass edit of an attribute from a variant group does not override group value
    Given I mass-edit products highheels, blue_highheels and sandals
    And I choose the "Edit common attributes" operation
    And I display the Heel Height attribute
    And I change the "Heel Height" to "3"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "heel_height" of products highheels, blue_highheels should be "12"
    And the metric "heel_height" of products sandals should be "3"

  Scenario: Successfully mass edit products and the completeness should be computed
    Given I am on the "sneakers" product page
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values                                                               | ratio |
      | mobile  | en_US  | warning | Name, Price, Size, Color                                                     | 20%   |
      | tablet  | en_US  | warning | Name, Description, Weather conditions, Price, Rating, Side view, Size, Color | 11%   |
    And I am on the "sandals" product page
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | missing_values                                           | ratio |
      | mobile  | en_US  | warning | Name, Price, Size, Color                                 | 20%   |
      | tablet  | en_US  | warning | Name, Description, Price, Rating, Side view, Size, Color | 13%   |
    Then I am on the products page
    And I mass-edit products sandals, sneakers
    And I choose the "Edit common attributes" operation
    And I display the Name, Price and Size attribute
    And I change the "Name" to "boots"
    Then I visit the "Marketing" group
    And I change the "Price" to "100 USD"
    And I change the "Price" to "150 EUR"
    Then I visit the "Sizes" group
    And I change the "Size" to "37"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then I am on the "sneakers" product page
    When I open the "Completeness" panel
    And I should see the completeness:
      | channel | locale | state   | missing_values                                            | ratio |
      | mobile  | en_US  | warning | Color                                                     | 80%   |
      | tablet  | en_US  | warning | Description, Weather conditions, Rating, Side view, Color | 44%   |
    And I am on the "sandals" product page
    When I open the "Completeness" panel
    And I should see the completeness:
      | channel | locale | state   | missing_values                        | ratio |
      | mobile  | en_US  | warning | Color                                 | 80%   |
      | tablet  | en_US  | warning | Description, Rating, Side view, Color | 50%   |
