@javascript
Feature: Edit common localized attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code       | attributes                                                       |
      | high_heels | sku, name, description, price, rating, size, color, manufacturer |
    And the following attributes:
      | code    | label  | type   | metric family | default metric unit | families       | decimals_allowed |
      | weight  | Weight | metric | Weight        | GRAM                | boots, sandals | yes              |
      | time    | Time   | number |               |                     | boots, sandals | yes              |
      | date    | Date   | date   |               |                     | boots, sandals |                  |
    And the following products:
      | sku            | family     |
      | boots          | boots      |
      | sandals        | sandals    |
    And I am logged in as "Julien"
    And I am on the products page

  Scenario: Successfully update many price values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Price attribute
    And I change the "Price" to "100,50 USD"
    And I change the "Price" to "150,75 EUR"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the prices "Price" of products boots and sandals should be:
      | amount | currency |
      | 100.50 | USD      |
      | 150.75 | EUR      |

  Scenario: Successfully update many metric values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Weight attribute
    And I change the "Weight" to "600,55"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "Weight" of products boots and sandals should be "600.55"

  Scenario: Successfully update many number values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Time attribute
    And I change the "Time" to "25,75"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "boots" should have the following value:
      | time | 25.75 |
    And the product "sandals" should have the following value:
      | time | 25.75 |

  Scenario: Successfully update many date values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Date attribute
    And I change the "Date" to "28/05/2015"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "boots" should have the following value:
      | date | 2015-05-28 |
    And the product "sandals" should have the following value:
      | date | 2015-05-28 |

  Scenario: Fail to update many price values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Price attribute
    And I change the "Price" to "100.50 USD"
    And I change the "Price" to "150.75 EUR"
    And I move on to the next step
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."

  Scenario: Fail to update many metric values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Weight attribute
    And I change the "Weight" to "600.55"
    And I move on to the next step
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."

  Scenario: Fail to update many number values at once
    Given I select rows boots and sandals
    And I press "Actions de masse" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Time attribute
    And I change the "Time" to "25.75"
    And I move on to the next step
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."
