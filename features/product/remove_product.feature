@javascript
Feature: Remove a product
  In order to delete an unnecessary product from my PIM
  As a product manager
  I need to be able to remove a product

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   | family |
      | jean  | pants  |
      | short | pants  |
    And I should get the following product drafts after apply the following updater to it:
      | product | actions                                                                                           | result | username |
      | jean    | [{"type": "set_data", "field": "name", "data": "Jean", "locale": "en_US", "scope": null}]         | {}     | Mary     |
      | jean    | [{"type": "set_data", "field": "name", "data": "Jean bootcut", "locale": "en_US", "scope": null}] | {}     | Sandra   |
      | short   | [{"type": "set_data", "field": "name", "data": "Short", "locale": "en_US", "scope": null}]        | {}     | Mary   |
    And I should get the following proposals:
      | product | username | result                                                                             |
      | jean    | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Jean"}]}}         |
      | jean    | Sandra   | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Jean bootcut"}]}} |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}}        |
    And I am logged in as "Julia"

  Scenario: Successfully mass delete a product and associated drafts
    Given I am on the products page
    And I select rows jean
    And I mass-delete products jean
    And I confirm the removal
    Then I should not see product jean
    Then I should get the following proposals:
      | product | username | result                                                                      |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}} |

  Scenario: Successfully delete a product and associated drafts
    Given I am on the "jean" product page
    And I press the "Delete" button
    Then I should see "Confirm deletion"
    And I confirm the removal
    Then I should not see product jean
    Then I should get the following proposals:
      | product | username | result                                                                      |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}} |
