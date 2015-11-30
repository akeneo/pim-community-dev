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
