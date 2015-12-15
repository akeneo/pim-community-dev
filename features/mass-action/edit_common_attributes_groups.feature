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
