@javascript
Feature: Download a product
  In order to view or share a product outside the PIM
  As a regular user
  I need to be able download a product

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Mary"
    And the following products:
      | sku    | family  |
      | sandal | sandals |

  Scenario: Successfully download a product
    Given I am on the "sandal" product page
    When I press the secondary action "PDF"
    # Then the response status code should be 200
    # Then the response content type should be "application/pdf"
