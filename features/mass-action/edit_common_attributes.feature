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
    And the following attribute:
      | code        | label       | type   | metric family | default metric unit | families                 |
      | weight      | Weight      | metric | Weight        | GRAM                | boots, sneakers, sandals |
      | heel_height | Heel Height | metric | Length        | CENTIMETER          | high_heels               |
    And the following products:
      | sku       | family     |
      | boots     | boots      |
      | sneakers  | sneakers   |
      | sandals   | sandals    |
      | pump      |            |
      | highheels | high_heels |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Allow editing only common attributes
    Given I mass-edit products boots, sandals and sneakers
    And I choose the "Edit attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Side view in group "Media"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I should see available attribute Weight in group "Other"

  @jira https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Allow editing only common attributes define from families
    Given I mass-edit products boots and highheels
    And I choose the "Edit attributes" operation
    Then I should see available attributes Name, Manufacturer and Description in group "Product information"
    And I should see available attributes Price and Rating in group "Marketing"
    And I should see available attribute Size in group "Sizes"
    And I should see available attribute Color in group "Colors"
    And I should not see available attribute SKU and Weather condition in group "Product information"
    And I should not see available attributes Side view and Top view in group "Media"
    And I should not see available attribute Lace color in group "Colors"
    And I should not see available attributes Heel height and Weight in group "Other"

  @jira https://akeneo.atlassian.net/browse/PIM-2182
  Scenario: Allow edition only common attribute to product values
    Given the following attributes:
      | code          | label         | unique |
      | sole_color    | Sole          | no     |
      | fur           | Fur           | no     |
      | serial_number | Serial number | yes    |
    And the following product values:
      | product   | attribute     | value                 |
      | boots     | comment       | Comment on boots      |
      | boots     | fur           | rabbit                |
      | boots     | serial_number | 123456789             |
      | highheels | comment       | Comment on high heels |
      | highheels | sole_color    | Red                   |
      | highheels | serial_number | 987654321             |
    When I mass-edit products boots and highheels
    And I choose the "Edit attributes" operation
    Then I should see available attribute Comment in group "Other"
    And I should not see available attributes Sole color, Fur and Serial number in group "Other"

  @jira https://akeneo.atlassian.net/browse/PIM-2183
  Scenario: Allow edition on common attributes with value not in family and no value on family
    Given the following attribute:
      | code       | label | families   |
      | sole_color | Sole  | high_heels |
    And the following product values:
      | product | attribute  | value |
      | boots   | sole_color | Blue  |
    When I mass-edit products boots and high_heels
    And I choose the "Edit attributes" operation
    Then I should see available attribute Sole in group "Other"

  Scenario: Successfully update many text values at once
    Given I mass-edit products boots, sandals and sneakers
    And I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    Then the english name of "boots" should be "boots"
    And the english name of "sandals" should be "boots"
    And the english name of "sneakers" should be "boots"

  @jira https://akeneo.atlassian.net/browse/PIM-3281
  Scenario: Successfully update localized values on selected locale
    Given I add the "french" locale to the "mobile" channel
    When I mass-edit products boots, sandals and sneakers
    And I choose the "Edit attributes" operation
    And I switch the locale to "French (France)"
    And I display the [name] attribute
    And I change the "[name]" to "chaussure"
    And I move on to the next step
    Then the french name of "boots" should be "chaussure"
    And the french name of "sandals" should be "chaussure"
    And the french name of "sneakers" should be "chaussure"

  @jira https://akeneo.atlassian.net/browse/PIM-3281
  Scenario: Successfully update localized and scoped values on selected locale
    Given I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And I set product "pump" family to "boots"
    And I mass-edit products boots and pump
    And I choose the "Edit attributes" operation
    And I switch the locale to "French (France)"
    And I display the [description] attribute
    And I expand the "[description]" attribute
    And fill in "pim_enrich_mass_edit_action_operation_values_description_mobile_text" with "Foo Fr"
    And fill in "pim_enrich_mass_edit_action_operation_values_description_tablet_text" with "Bar Fr"
    And I move on to the next step
    Then the french mobile description of "boots" should be "Foo Fr"
    And the french tablet description of "boots" should be "Bar Fr"
    And the french mobile description of "pump" should be "Foo Fr"
    And the french tablet description of "pump" should be "Bar Fr"

  Scenario: Successfully update many price values at once
    Given I mass-edit products boots and sandals
    And I choose the "Edit attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "100"
    And I change the "€ Price" to "150"
    And I move on to the next step
    Then the prices "Price" of products boots and sandals should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  Scenario: Successfully update many file values at once
    Given I mass-edit products sandals and sneakers
    And I choose the "Edit attributes" operation
    And I display the Side view attribute
    And I attach file "SNKRS-1R.png" to "Side view"
    And I move on to the next step
    Then the file "side_view" of products sandals and sneakers should be "SNKRS-1R.png"

  Scenario: Successfully update many multi-valued values at once
    Given I mass-edit products boots and sneakers
    And I choose the "Edit attributes" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I move on to the next step
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |

  Scenario: Successfully update many metric values at once
    Given I mass-edit products boots and sandals
    And I choose the "Edit attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I move on to the next step
    Then the metric "Weight" of products boots and sandals should be "600"

  Scenario: Successfully translate groups and labels
    Given I add the "french" locale to the "mobile" channel
    And the following attribute label translations:
      | attribute | locale | label  |
      | name      | french | Nom    |
      | size      | french | Taille |
    When I mass-edit products boots and sandals
    And I choose the "Edit attributes" operation
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
    And I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    Then the english name of "pump" should be "boots"
    And the english name of "sneakers" should be "boots"

  @info https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Successfully mass edit scoped product values
    Given I set product "pump" family to "boots"
    When I mass-edit products boots and pump
    And I choose the "Edit attributes" operation
    And I display the Description attribute
    And I expand the "Description" attribute
    And fill in "pim_enrich_mass_edit_action_operation_values_description_mobile_text" with "Foo"
    And fill in "pim_enrich_mass_edit_action_operation_values_description_tablet_text" with "Bar"
    And I move on to the next step
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
    And I am on the products page
    When I mass-edit products Shoes
    And I choose the "Edit attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "100"
    And I change the "€ Price" to "150"
    And I move on to the next step
    Then the prices "Price" of products Shoes should be:
      | amount | currency |
      | 100    | USD      |
      | 150    | EUR      |

  @jira https://akeneo.atlassian.net/browse/PIM-3282
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
    And I choose the "Edit attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I move on to the next step
    Then the metric "Weight" of products boots and sneakers should be "600"
    And the metric "Weight" of products sandals, pump and highheels should be "500"
