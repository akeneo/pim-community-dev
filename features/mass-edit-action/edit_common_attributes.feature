@javascript
Feature: Edit common attributes of many products at once
  In order to update many products with the same information
  As Julia
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following attribute:
      | code   | label  | type   | metric family | default metric unit | families                 |
      | weight | Weight | metric | Weight        | GRAM                | boots, sneakers, sandals |
    And the following products:
     | sku      | family   |
     | boots    | boots    |
     | sneakers | sneakers |
     | sandals  | sandals  |
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

  Scenario: Succesfully update many text values at once
    Given I mass-edit products boots, sandals and sneakers
    And I choose the "Edit attributes" operation
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    Then the english name of "boots" should be "boots"
    And the english name of "sandals" should be "boots"
    And the english name of "sneakers" should be "boots"

  Scenario: Succesfully update many price values at once
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

  Scenario: Succesfully update many file values at once
    Given I mass-edit products sandals and sneakers
    And I choose the "Edit attributes" operation
    And I display the Side view attribute
    And I attach file "SNKRS-1R.png" to "Side view"
    And I move on to the next step
    Then the file "side_view" of products sandals and sneakers should be "SNKRS-1R.png"

  Scenario: Succesfully update many multi-valued values at once
    Given I mass-edit products boots and sneakers
    And I choose the "Edit attributes" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I move on to the next step
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |

  Scenario: Succesfully update many metric values at once
    Given I mass-edit products boots and sandals
    And I choose the "Edit attributes" operation
    And I display the Weight attribute
    And I change the "Weight" to "600"
    And I move on to the next step
    Then the metric "Weight" of products boots and sandals should be "600"

  Scenario: Succesfully translate groups and labels
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
