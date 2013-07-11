@javascript
Feature: Add metric attribute to a product
  In order to provide more information about a product
  As an user
  I need to be able to add a metric attribute to a product

  Background:
    Given a "Car" product available in english
    And the following product attribute:
      | type   | label  | metric family | default metric unit |
      | metric | Length | Length        | meter               |
    And I am logged in as "admin"

  Scenario: Successfully add a metric attribute to a product
    Given I am on the "Car" product page
    And I add available attribute Length
    Then attributes in group "Other" should be SKU and Length
