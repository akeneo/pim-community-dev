@javascript
Feature: When I mass edit I should be able to see how many items will be edited

  Background:
    Given a "footwear" catalog configuration
    And the following family:
      | code       | attributes                                                       |
      | high_heels | sku, name, description, price, rating, size, color, manufacturer |
    And the following attributes:
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
      | shoe_1    | high_heels |
      | shoe_2    | high_heels |
      | shoe_3    | high_heels |
      | shoe_4    | high_heels |
      | shoe_5    | high_heels |
      | shoe_6    | high_heels |
      | shoe_7    | high_heels |
      | shoe_8    | high_heels |
      | shoe_9    | high_heels |
      | shoe_10   | high_heels |
      | shoe_11   | high_heels |
      | shoe_12   | high_heels |
      | shoe_13   | high_heels |
      | shoe_14   | high_heels |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Successfully count the number of mass-edited items when click on all products
    Given I select all products
    When I press mass-edit button
    Then I should see "Mass Edit (19 products)"

  Scenario: Successfully count the number of mass-edited items when click on all visible products
    Given I select all visible products
    When I press mass-edit button
    Then I should see "Mass Edit (10 products)"

  Scenario: Successfully count the number of mass-edited items by select them one by one
    Given I mass-edit products boot, shoe_1, shoe_14
    Then I should see "Mass Edit (3 products)"

  Scenario: Successfully count the number of mass-edited items when using filters and select all action
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
    When I press mass-edit button
    Then I should see "Mass Edit (2 products)"
