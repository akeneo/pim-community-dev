@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label-en_US | type               | metric_family | default_metric_unit | group | decimals_allowed | negative_allowed |
      | weight      | Weight      | pim_catalog_metric | Weight        | GRAM                | other | 0                | 0                |
      | heel_height | Heel Height | pim_catalog_metric | Length        | CENTIMETER          | other | 0                | 0                |
    And the following family:
      | code       | attributes                                                                                                        |
      | high_heels | sku,name,description,price,rating,size,color,manufacturer,heel_height                                             |
      | boots      | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight |
      | sneakers   | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight |
      | sandals    | sku,name,manufacturer,description,price,rating,side_view,size,color,weight,heel_height                            |
    And the following products:
      | sku            | family     | color | heel_height   | groups |
      | boots          | boots      |       |               |        |
      | sneakers       | sneakers   |       |               |        |
      | sandals        | sandals    |       |               |        |
      | pump           |            |       |               |        |
      | highheels      | high_heels | red   | 12 CENTIMETER |        |
      | blue_highheels | high_heels | blue  | 12 CENTIMETER |        |
    And I am logged in as "Julia"

  Scenario: Allow editing all attributes on configuration screen
    Given I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Side view in group "Media"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I should see available attribute Weight in group "Other"

  @jira https://akeneo.atlassian.net/browse/PIM-6273
  Scenario: Successfully remove product attribute fields
    Given I am on the products grid
    And I select rows boots, sandals and sneakers
    When I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Name attribute
    Then I should see a remove link next to the "Name" field
    When I remove the "Name" attribute
    Then I should not see the "Name" field
    And I should not see a remove link next to the "Name" field

  @critical
  Scenario: Successfully update many text values at once
    Given I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    Then I should see a remove link next to the "Name" field
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the english localizable value name of "boots" should be "boots"
    And the english localizable value name of "sandals" should be "boots"
    And the english localizable value name of "sneakers" should be "boots"

  @critical
  Scenario: Successfully update many multi-valued values at once
    Given I am on the products grid
    And I select rows boots and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |

  @critical
  @info https://akeneo.atlassian.net/browse/PIM-3070
  Scenario: Successfully mass edit a price not added to the product
    Given I am on the products grid
    And I collapse the column
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | Shoes |
      | Family | Heels |
    And I press the "Save" button in the popin
    Then I should be on the product "Shoes" edit page
    And I am on the products grid
    When I select row Shoes
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Price attribute
    And I change the "Price" to "100 USD"
    And I change the "Price" to "150 EUR"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the prices "Price" of products Shoes should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  @critical
  @jira https://akeneo.atlassian.net/browse/PIM-6008
  Scenario: Successfully mass edit scoped product values with special chars
    Given I am on the products grid
    And I set product "pump" family to "boots"
    When I select rows boots and pump
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Description attribute
    And I change the Description to "&$@(B°ar'<"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the english tablet Description of "boots" should be "&$@(B°ar'<"
    And the english tablet Description of "pump" should be "&$@(B°ar'<"

  @jira https://akeneo.atlassian.net/browse/PIM-6240
  Scenario: Allow editing all attributes on configuration screen
    Given I am on the "tablet" channel page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) |  |
    And I press the "Save" button
    Then I should not see the text "My tablet"
    And I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    When I choose the "Edit attributes values" operation
    Then I should see the text "[tablet]"
    And I should not see the text "undefined"

  @jira https://akeneo.atlassian.net/browse/PIM-6274
  Scenario: Successfully validate products with a custom validation on identifier
    Given I am on the "SKU" attribute page
    When I fill in the following information:
      | Validation rule    | Regular expression |
      | Regular expression | /^\d+$/            |
    And I press the "Save" button
    And I should not see the text "There are unsaved changes."
    And I am on the products grid
    Given I select rows boots, sandals and sneakers
    When I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Name attribute
    And I move on to the next step
    Then I should not see the text "There are errors in the attributes form"
