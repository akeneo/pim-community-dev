@javascript
Feature: Assign assets to a product
  In order to assign assets to a product
  As a product manager
  I need to be able to link multiple assets to a product

  Background:
    Given the "clothing" catalog configuration
    And the following assets:
    | code       | tags             | description            | end of use at |
    | blue_shirt | solid_color, men | A beautiful blue shirt | now           |
    | red_shirt  | solid_color, men | A beautiful red shirt  | now           |
    And the following products:
      | sku   |
      | shirt |
    And I am logged in as "Julia"

  Scenario: Succesfully assign assets to a product
    Given I am on the "shirt" product page
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I check the row "paint"
    And I check the row "machine"
    Then the asset basket should contain paint, machine
    And I confirm the asset modification
    Then the "Front view" asset gallery should contains paint, machine
    And I save the product
    Then the "Front view" asset gallery should contains paint, machine
    And I start to manage assets for "Front view"
    And I change the page size to 100
    And I uncheck the row "paint"
    And I check the row "dog"
    And I check the row "akene"
    And I remove "machine" from the asset basket
    Then the asset basket should contain akene, dog
    And I confirm the asset modification
    Then the "Front view" asset gallery should contains akene, dog
    And I save the product
    Then the "Front view" asset gallery should contains akene, dog
