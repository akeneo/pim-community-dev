@javascript
Feature: Save a product when a channel has been removed
  In order to delete an unnecessary channel
  As a product manager
  I need to be able to continue to work with products which used the channel

  Background:
    Given the "footwear" catalog configuration
    And the following attributes:
      | code        | type                 | label-en_US | group |
      | description | pim_catalog_textarea | Description | info  |
    And the following family:
      | code      | attributes      |
      | vegetable | sku,description |
    And the following products:
      | sku       | family    | description-en_US-tablet |
      | artichoke | vegetable | Yummy !                  |
    And I am logged in as "Julia"

  Scenario: Successfully delete a channel and continue to work with products which used the channel
    Given I am on the channels page
    And I click on the "Delete" action of the row which contains "Tablet"
    And I confirm the deletion
    Given I am on the "artichoke" product page
    And I fill in the following information:
      | Description | Finally artichokes are not so good |
    And I save the product
    Then the product Description should be "Finally artichokes are not so good"
