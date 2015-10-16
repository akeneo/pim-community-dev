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
      | code         | label       | type   | metric family | default metric unit | families                 |
      | weight       | Weight      | metric | Weight        | GRAM                | boots, sneakers, sandals |
      | heel_height  | Heel Height | metric | Length        | CENTIMETER          | high_heels, sandals      |
      | buckle_color | Buckle      | text   |               |                     | high_heels               |
    And the following product groups:
      | code          | label         | axis  | type    |
      | variant_heels | Variant Heels | color | VARIANT |
    And the following variant group values:
      | group         | attribute   | value         |
      | variant_heels | heel_height | 12 CENTIMETER |
    And the following products:
      | sku            | family     | color  | groups        |
      | boots          | boots      |        |               |
      | sneakers       | sneakers   |        |               |
      | sandals        | sandals    |        |               |
      | pump           |            |        |               |
      | highheels      | high_heels | red    | variant_heels |
      | blue_highheels | high_heels | blue   | variant_heels |
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

  @jira https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Allow editing only common attributes define from families
    Given I mass-edit products boots and highheels
    And I choose the "Edit common attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I display the Name and Weather condition attribute
    And I change the "Weather condition" to "Cold, Wet"
    And I change the "Name" to "Product"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "boots" should have the following values:
      | name-en_US         | Product      |
      | weather_conditions | [wet], [cold] |
    And the product "highheels" should have the following values:
      | name-en_US | Product  |

  @jira https://akeneo.atlassian.net/browse/PIM-2183
  Scenario: Allow edition on common attributes with value not in family and no value on family
    Given the following product values:
      | product | attribute    | value |
      | boots   | buckle_color | Blue  |
    When I mass-edit products boots and high_heels
    And I choose the "Edit common attributes" operation
    Then I should see available attribute Buckle in group "Other"

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

  @jira https://akeneo.atlassian.net/browse/PIM-3281
  Scenario: Successfully update localized values on selected locale
    Given I add the "french" locale to the "mobile" channel
    When I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I switch the locale to "French (France)"
    And I display the [name] attribute
    And I change the "[name]" to "chaussure"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the french name of "boots" should be "chaussure"
    And the french name of "sandals" should be "chaussure"
    And the french name of "sneakers" should be "chaussure"

  @jira https://akeneo.atlassian.net/browse/PIM-3281
  Scenario: Successfully update localized and scoped values on selected locale
    Given I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And I set product "pump" family to "boots"
    And I mass-edit products boots and pump
    And I choose the "Edit common attributes" operation
    And I switch the locale to "French (France)"
    And I display the [description] attribute
    And I expand the "[description]" attribute
    And fill in "pim_enrich_mass_edit_choose_action_operation_values_description_mobile_text" with "Foo Fr"
    And fill in "pim_enrich_mass_edit_choose_action_operation_values_description_tablet_text" with "Bar Fr"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the french mobile description of "boots" should be "Foo Fr"
    And the french tablet description of "boots" should be "Bar Fr"
    And the french mobile description of "pump" should be "Foo Fr"
    And the french tablet description of "pump" should be "Bar Fr"

  Scenario: Successfully update many price values at once
    Given I mass-edit products boots and sandals
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "100"
    And I change the "€ Price" to "150"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the prices "Price" of products boots and sandals should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  Scenario: Successfully update many images values at once
    Given I mass-edit products sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Side view attribute
    And I attach file "SNKRS-1R.png" to "Side view"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the file "side_view" of products sandals and sneakers should be "SNKRS-1R.png"

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

  Scenario: Successfully update many metric values at once
    Given I mass-edit products boots and sandals
    And I choose the "Edit common attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "Weight" of products boots and sandals should be "600"

  Scenario: Successfully translate groups and labels
    Given I add the "french" locale to the "mobile" channel
    And the following attribute label translations:
      | attribute | locale | label  |
      | name      | french | Nom    |
      | size      | french | Taille |
    When I mass-edit products boots and sandals
    And I choose the "Edit common attributes" operation
    And I display the Name and Size attributes
    Then I should see "Product information"
    And I should see "Sizes"
    And I should see "Name"
    And I should see "Size"
    When I switch the locale to "French (France)"
    Then I should see "[info]"
    And I should see "[sizes]"
    And I should see "Nom"
    And I should see "Taille"

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

  @info https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Successfully mass edit scoped product values
    Given I set product "pump" family to "boots"
    When I mass-edit products boots and pump
    And I choose the "Edit common attributes" operation
    And I display the Description attribute
    And I expand the "Description" attribute
    And fill in "pim_enrich_mass_edit_choose_action_operation_values_description_mobile_text" with "Foo"
    And fill in "pim_enrich_mass_edit_choose_action_operation_values_description_tablet_text" with "Bar"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the english mobile Description of "boots" should be "Foo"
    And the english tablet Description of "boots" should be "Bar"
    And the english mobile Description of "pump" should be "Foo"
    And the english tablet Description of "pump" should be "Bar"

  @info https://akeneo.atlassian.net/browse/PIM-3070
  Scenario: Successfully mass edit a price not added to the product
    Given I create a new product
    And I fill in the following information in the popin:
      | SKU    | Shoes      |
      | Family | high_heels |
    And I press the "Save" button in the popin
    Then I should be on the product "Shoes" edit page
    And I am on the products page
    When I mass-edit products Shoes
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "100"
    And I change the "€ Price" to "150"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the prices "Price" of products Shoes should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  @jira https://akeneo.atlassian.net/browse/PIM-3282, https://akeneo.atlassian.net/browse/PIM-3880
  Scenario: Successfully mass edit products on the non default channel
    Given the following product values:
      | product   | attribute                | value                   |
      | boots     | description-en_US-tablet | A beautiful description |
      | boots     | weight                   | 500 GRAM                |
      | sneakers  | description-en_US-tablet | A beautiful description |
      | sneakers  | weight                   | 500 GRAM                |
      | sandals   | weight                   | 500 GRAM                |
      | pump      | weight                   | 500 GRAM                |
      | highheels | weight                   | 500 GRAM                |
    When I show the filter "Description"
    And I filter by "Channel" with value "Tablet"
    And I filter by "Description" with value "A beautiful description"
    And I select all products
    And I press mass-edit button
    And I choose the "Edit common attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "Weight" of products boots and sneakers should be "600"
    And the metric "Weight" of products sandals, pump and highheels should be "500"

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
    And I change the "Weight" to "Edith"
    And I move on to the next step
    Then I should see "Product information"
    And I should see "Weight"
    And I should see "Name"
    When I am on the attributes page
    And I am on the products page
    And I mass-edit products boots and sandals
    And I choose the "Edit common attributes" operation
    Then I should not see "Product information"
    And I should not see "Weight"
    And I should not see "Name"

  @jira https://akeneo.atlassian.net/browse/PIM-4777
  Scenario: Doing a mass edit of an attribute from a variant group does not override group value
    Given I mass-edit products highheels, blue_highheels and sandals
    And I choose the "Edit common attributes" operation
    And I display the Heel Height attribute
    And fill in "Heel Height" with "3"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "heel_height" of products highheels, blue_highheels should be "12"
    And the metric "heel_height" of products sandals should be "3"
