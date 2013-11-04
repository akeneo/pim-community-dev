Feature: Display the product group history
  In order to know who, when and what changes has been made to a product group
  As a user
  I need to have access to a product group history

  Background:
    Given there is no product group
    And the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
    And I am logged in as "admin"

  @javascript
  Scenario: Succesfully edit a iproduct group and see the history
    Given I am on the product groups page
    When I create a new product group
    And I fill in the following information in the popin:
      | Code | AkeneoShirt |
    And I select the axis "Color"
    And I press the "Save" button
    Then I am on the product groups page
    And I should see group AkeneoShirt
    When I am on the "AkeneoShirt" product group page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | data                                         |
      | create | 1       | code:AkeneoShirttype:VARIANTattributes:color |

