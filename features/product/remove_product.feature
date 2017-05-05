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
      | short   | [{"type": "set_data", "field": "name", "data": "Short", "locale": "en_US", "scope": null}]        | {}     | Mary     |
    And I should get the following proposals:
      | product | username | result                                                                                                                                                                   |
      | jean    | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Jean"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}}         |
      | jean    | Sandra   | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Jean bootcut"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}} |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}}        |
    And I am logged in as "Julia"

  Scenario: Successfully mass delete a product and associated drafts
    Given I am on the products page
    And I select row jean
    And I press "Delete" on the "Bulk Actions" dropdown button
    And I confirm the removal
    Then I should not see product jean
    Then I should get the following proposals:
      | product | username | result                                                                                                                                                            |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}} |

  Scenario: Successfully delete a product and associated drafts
    Given I am on the "jean" product page
    And I press the "Delete" button
    Then I should see "Confirm deletion"
    And I confirm the removal
    Then I should not see product jean
    Then I should get the following proposals:
      | product | username | result                                                                                                                                                            |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}} |

  Scenario: Not being able to delete a published product
    Given I am on the "jean" product page
    And I publish the product "jean"
    When I press the "Delete" button
    And I confirm the removal
    Then I should see the text "Impossible to remove a published product"
    When I am on the products page
    Then I should see product jean
