@javascript
Feature: Edit common localized attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code   | label-en_US | type               | metric_family | default_metric_unit | decimals_allowed | negative_allowed | group |
      | weight | Weight      | pim_catalog_metric | Weight        | GRAM                | 1                | 0                | other |
      | time   | Time        | pim_catalog_number |               |                     | 1                | 0                | other |
      | date   | Date        | pim_catalog_date   |               |                     |                  |                  | other |
    And the following family:
      | code       | attributes                                                                                                                  |
      | high_heels | sku,name,description,price,rating,size,color,manufacturer                                                                   |
      | boots      | sku,name,manufacturer,description,weather_conditions,price,rating,side_view,top_view,size,color,lace_color,weight,time,date |
      | sandals    | sku,name,manufacturer,description,price,rating,side_view,size,color,weight,time,date                                        |
    And the following products:
      | sku     | family  |
      | boots   | boots   |
      | sandals | sandals |
    And I am logged in as "Julien"
    And I am on the products page

  Scenario: Successfully update many price values at once
    Given I select rows boots and sandals
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
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
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Weight attribute
    And I change the "Weight" to "600,55"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "Weight" of products boots and sandals should be "600.55"

  Scenario: Successfully update many number values at once
    Given I select rows boots and sandals
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
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
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
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
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Price attribute
    And I change the "Price" to "100.50 USD"
    And I change the "Price" to "150.75 EUR"
    And I move on to the next step
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."

  Scenario: Fail to update many metric values at once
    Given I select rows boots and sandals
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Weight attribute
    And I change the "Weight" to "600.55"
    And I move on to the next step
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."

  Scenario: Fail to update many number values at once
    Given I select rows boots and sandals
    And I press "Modifier les informations du produit" on the "Actions de masse" dropdown button
    When I choose the "Modifier les attributs communs" operation
    And I display the Time attribute
    And I change the "Time" to "25.75"
    And I move on to the next step
    Then I should see validation error "Ce type de valeur attend une virgule (,) comme séparateur de décimales."
