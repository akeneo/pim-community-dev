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
      | SKU    | Shoes      |
      | Family | high_heels |
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

  @info https://akeneo.atlassian.net/browse/PIM-2163
  Scenario: Successfully mass edit scoped product values
    Given I set product "pump" family to "boots"
    When I mass-edit products boots and pump
    And I choose the "Edit common attributes" operation
    And I display the Description attribute
    And I change the Description to "Bar"
    And I switch the scope to "mobile"
    And I change the "Description" to "Foo"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the english mobile Description of "boots" should be "Foo"
    And the english tablet Description of "boots" should be "Bar"
    And the english mobile Description of "pump" should be "Foo"
    And the english tablet Description of "pump" should be "Bar"
