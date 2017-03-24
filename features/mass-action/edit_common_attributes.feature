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
    And the following variant groups:
      | code          | label-en_US   | axis  | type    |
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
    Given I select rows boots, sandals and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Side view in group "Media"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I should see available attribute Weight in group "Other"

  Scenario: Successfully update many text values at once
    Given I select rows boots, sandals and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the english name of "boots" should be "boots"
    And the english name of "sandals" should be "boots"
    And the english name of "sneakers" should be "boots"

  Scenario: Successfully update many multi-valued values at once
    Given I select rows boots and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
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
    When I select rows pump and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
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
    When I select row Shoes
    And I press "Change product information" on the "Bulk Actions" dropdown button
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
    Given I select rows boots and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
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
    Given I select rows boots and sandals
    And I press "Change product information" on the "Bulk Actions" dropdown button
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
    And I select rows boots and sandals
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    Then I should not see the text "Product information"
    And I should not see the text "Weight"
    And I should not see the text "Name"

  @jira https://akeneo.atlassian.net/browse/PIM-4777
  Scenario: Doing a mass edit of an attribute from a variant group does not override group value
    Given I select rows highheels, blue_highheels and sandals
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Heel Height attribute
    And I change the "Heel Height" to "3"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "heel_height" of products highheels, blue_highheels should be "12"
    And the metric "heel_height" of products sandals should be "3"

  @jira https://akeneo.atlassian.net/browse/PIM-6008
  Scenario: Successfully mass edit scoped product values with special chars
    Given I set product "pump" family to "boots"
    When I select rows boots and pump
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Description attribute
    And I change the Description to "&$@(B°ar'<"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    And the english tablet Description of "boots" should be "&$@(B°ar'<"
    And the english tablet Description of "pump" should be "&$@(B°ar'<"

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
    And I select rows sandals, sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
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
    Then I am on the products page
    And I should see the text "44"
    And I should see the text "50"
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

  @jira https://akeneo.atlassian.net/browse/PIM-6022
  Scenario: Successfully mass edit product values preventing Shell Command Injection
    Given I am on the "boots" family page
    And I visit the "Attributes" tab
    And I add available attributes Comment
    And I save the family
    And I should not see the text "There are unsaved changes."
    And I am on the "sneakers" family page
    And I visit the "Attributes" tab
    And I add available attributes Comment
    And I save the family
    And I should not see the text "There are unsaved changes."
    And I am on the "sandals" family page
    And I visit the "Attributes" tab
    And I add available attributes Comment
    And I save the family
    And I should not see the text "There are unsaved changes."
    And I am on the products page
    When I select rows boots, sandals and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I display the Description and Name and Comment attribute
    And I change the "Name" to "\$\(touch \/tmp\/inject.txt\) && \$\$ || `ls`; \"echo \"SHELL_INJECTION\"\""
    And I change the "Description" to ";`echo \"SHELL_INJECTION\"`"
    And I visit the "Other" group
    And I change the "Comment" to "$(echo "shell_injection" > shell_injection.txt)"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the english name of "boots" should be "\$\(touch \/tmp\/inject.txt\) && \$\$ || `ls`; \"echo \"SHELL_INJECTION\"\""
    And the english name of "sandals" should be "\$\(touch \/tmp\/inject.txt\) && \$\$ || `ls`; \"echo \"SHELL_INJECTION\"\""
    And the english name of "sneakers" should be "\$\(touch \/tmp\/inject.txt\) && \$\$ || `ls`; \"echo \"SHELL_INJECTION\"\""
    And the english tablet description of "boots" should be ";`echo \"SHELL_INJECTION\"`"
    And the english tablet description of "sandals" should be ";`echo \"SHELL_INJECTION\"`"
    And the english tablet description of "sneakers" should be ";`echo \"SHELL_INJECTION\"`"
    And attribute Comment of "boots" should be "$(echo "shell_injection" > shell_injection.txt)"
    And attribute Comment of "sandals" should be "$(echo "shell_injection" > shell_injection.txt)"
    And attribute Comment of "sneakers" should be "$(echo "shell_injection" > shell_injection.txt)"
    And file "%web%shell_injection.txt" should not exist
