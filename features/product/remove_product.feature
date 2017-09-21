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

  Scenario: Successfully mass delete a product and associated drafts
    Given I am logged in as "Julia"
    And I am on the products grid
    And I select row jean
    And I press the "Delete" button
    And I confirm the removal
    Then I should not see product jean
    Then I should get the following proposals:
      | product | username | result                                                                                                                                                            |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}} |

  Scenario: Successfully delete a product and associated drafts
    Given I am logged in as "Julia"
    And I am on the "jean" product page
    And I press the secondary action "Delete"
    Then I should see the text "Confirm deletion"
    And I confirm the removal
    Then I should not see product jean
    Then I should get the following proposals:
      | product | username | result                                                                                                                                                            |
      | short   | Mary     | {"values": {"name": [{"locale": "en_US", "scope": null, "data": "Short"}]}, "review_statuses": {"name": [{"locale": "en_US", "scope": null, "status": "draft"}]}} |

  Scenario: Not being able to delete a published product
    Given I am logged in as "Julia"
    And I am on the "jean" product page
    And I publish the product "jean"
    When I press the secondary action "Delete"
    And I confirm the removal
    Then I should see the text "Impossible to remove a published product"
    When I am on the products grid
    Then I should see product jean

  Scenario: Not being able to delete a product from the edit form without having sufficient permissions
    Given the following products:
      | sku       | categories |
      | edit_only | tees       |
      | view_only | pants      |
    And I am logged in as "Mary"
    When I am on the "edit_only" product page
    Then I should not see the secondary action "Delete"
    When I am on the "view_only" product page
    Then I should not see the secondary action "Delete"

  Scenario: Not being able to delete a product from the grid without having sufficient permissions
    Given the following products:
      | sku       | categories |
      | edit_only | tees       |
      | view_only | pants      |
    And I am logged in as "Mary"
    When I am on the products grid
    Then I should not be able to view the "Delete the product" action of the row which contains "edit_only"
    And I should not be able to view the "Delete the product" action of the row which contains "view_only"
