@javascript
Feature: Approve or refuse several product drafts at once
  In order to control which data should be applied to products
  As a product manager
  I need to be able to approve or refuse several proposals at the same time

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku            | family  | categories | name-en_US     | legacy_attribute |
      | leather-jacket | jackets | jackets    | Leather jacket |                  |
      | wool-jacket    | jackets | jackets    | Wool jacket    |                  |
    And I am logged in as "Julia"

  Scenario: Successfully approve several proposals
    Given the following product drafts:
      | product        | status | author                               | result                                                                                                                                                                   |
      | leather-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Approve all selected" button
    And I confirm the action
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 2"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"
    When I edit the "wool-jacket" product
    Then the product Name should be "Lame wool jacket"

  Scenario: Unsuccessfully approve proposals that contain values I can't edit
    Given the following product drafts:
      | product        | status | author                               | result                                                                                                                                                                               |
      | leather-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"legacy_attribute":[{"locale":"en_US","scope":null,"data":"Dumb value"}]}, "review_statuses":{"legacy_attribute":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}                   |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Approve all selected" button
    And I confirm the action
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 1"
    And I should see the text "Skipped 1"
    And I should see the text "You can't edit the attributes modified by this proposal"

  Scenario: Successfully reject several proposals
    Given the following product drafts:
      | product        | status | author                               | result                                                                                                                                                                   |
      | leather-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Reject all selected" button
    And I confirm the action
    And I wait for the "refuse_product_draft" job to finish
    And I go on the last executed job resume of "refuse_product_draft"
    Then I should see the text "rejected 2"
    When I edit the "leather-jacket" product
    Then the product Name should be "Leather jacket"
    When I edit the "wool-jacket" product
    Then the product Name should be "Wool jacket"

  Scenario: Unsuccessfully reject proposals that contain values I can't edit
    Given the following product drafts:
      | product        | status | author                               | result                                                                                                                                                                               |
      | leather-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"legacy_attribute":[{"locale":"en_US","scope":null,"data":"Dumb value"}]}, "review_statuses":{"legacy_attribute":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}                   |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Reject all selected" button
    And I confirm the action
    And I wait for the "refuse_product_draft" job to finish
    And I go on the last executed job resume of "refuse_product_draft"
    Then I should see the text "rejected 1"
    And I should see the text "Skipped 1"
    And I should see the text "You can't edit the attributes modified by this proposal"

  Scenario: Successfully approve all proposals
    Given the following product drafts:
      | product        | status | author                               | result                                                                                                                                                                   |
      | leather-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    And I select rows Leather jacket
    When I select all entities
    And I press the "Approve all selected" button
    And I confirm the action
    Then I should not see "Sorry, page was not loaded correctly"
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 2"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"
    When I edit the "wool-jacket" product
    Then the product Name should be "Lame wool jacket"

  Scenario: Successfully approve user proposals
    Given the following product drafts:
      | product        | status | author   | result                                                                                                                                                                   |
      | leather-jacket | ready  | user_one | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | user_two | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    Then I filter by "author" with operator "equals" and value "user_one"
    And I select rows Leather jacket
    And I press the "Approve all selected" button
    And I confirm the action
    Then I should not see "Sorry, page was not loaded correctly"
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 1"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"

  Scenario: Successfully approve proposals between dates
    Given the following product drafts:
      | product        | status | author                               | result                                                                                                                                                                   | createdAt           |
      | leather-jacket | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} | 2014-01-01 00:00:00 |
      | wool-jacket    | ready  | csv_clothing_product_proposal_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       | 2015-01-01 00:00:00 |
    And I am on the proposals page
    And I filter by "createdAt" with operator "between" and value "06/01/2013 and 06/01/2014"
    Then the grid should contain 1 element
    When I select rows Leather jacket
    And I press the "Approve all selected" button
    And I confirm the action
    Then I should not see "Sorry, page was not loaded correctly"
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 1"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"

  Scenario: Successfully approve all proposal but one
    Given the following product drafts:
      | product        | status | author   | result                                                                                                                                                                   |
      | leather-jacket | ready  | user_one | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | user_two | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    And I select rows Leather jacket
    When I select all entities
    When I unselect rows Wool jacket
    And I press the "Approve all selected" button
    And I confirm the action
    Then I should not see "Sorry, page was not loaded correctly"
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 1"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"
