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
      | product        | status | source | source_label | author                               | author_label                         | result                                                                                                                                                                   |
      | leather-jacket | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
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
      | product        | status | source | source_label | author                               | author_label                         | result                                                                                                                                                                         |
      | leather-jacket | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"legacy_attribute":[{"locale":null,"scope":null,"data":"Dumb value"}]}, "review_statuses":{"legacy_attribute":[{"locale":null,"scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}             |
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
      | product        | status | source | source_label | author                               | author_label                         | result                                                                                                                                                                   |
      | leather-jacket | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
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
      | product        | status | source | source_label | author                               | author_label                         | result                                                                                                                                                                         |
      | leather-jacket | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"legacy_attribute":[{"locale":null,"scope":null,"data":"Dumb value"}]}, "review_statuses":{"legacy_attribute":[{"locale":null,"scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}             |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Reject all selected" button
    And I confirm the action
    And I wait for the "refuse_product_draft" job to finish
    And I go on the last executed job resume of "refuse_product_draft"
    Then I should see the text "rejected 1"
    And I should see the text "Skipped 1"
    And I should see the text "You can't edit the attributes modified by this proposal"

    #| 100 |             309 | NULL | Julia |      2 | NULL                | NULL                | 2021-03-25 08:33:00 | NULL                | NULL                | UNKNOWN   |                  | a:0:{}             | NULL                                                                          | {"comment": null, "user_to_notify": "Julia", "productDraftIds": [17, 18], "realTimeVersioning": true, "productModelDraftIds": [], "is_user_authenticated": null} |
    # | 47 |             131 |  113 | Julia |      1 | 2021-03-25 08:43:30 | 2021-03-25 08:43:30 | 2021-03-25 08:43:29 | 2021-03-25 08:43:29 | 2021-03-25 08:43:29 | COMPLETED |                  | a:0:{}             | /srv/pim/var/logs/batch/47/batch_44f3a9e952730bb4a48b419bdf6d0e773ef748d1.log | {"comment": null, "user_to_notify": "Julia", "productDraftIds": [1, 2], "realTimeVersioning": true, "productModelDraftIds": [], "is_user_authenticated": null} |

  Scenario: Successfully approve all proposals
    Given the following product drafts:
      | product        | status | source | source_label | author                               | author_label                         | result                                                                                                                                                                   |
      | leather-jacket | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | pim    | PIM          | csv_clothing_product_proposal_import | CSV Clothing Product Proposal Import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    And I select rows Leather jacket
    When I select all entities
    And I press the "Approve all selected" button
    And I confirm the action
    Then I should not see the text "Sorry, page was not loaded correctly"
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 2"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"
    When I edit the "wool-jacket" product
    Then the product Name should be "Lame wool jacket"

  Scenario: Successfully approve all proposal but one
    Given the following product drafts:
      | product        | status | source | source_label | author   | author_label | result                                                                                                                                                                   |
      | leather-jacket | ready  | pim    | PIM          | user_one | User One     | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}} |
      | wool-jacket    | ready  | pim    | PIM          | user_two | User Two     | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}, "review_statuses":{"name":[{"locale":"en_US","scope":null,"status":"to_review"}]}}       |
    And I am on the proposals page
    And I select rows Leather jacket
    When I select all entities
    When I unselect rows Wool jacket
    And I press the "Approve all selected" button
    And I confirm the action
    Then I should not see the text "Sorry, page was not loaded correctly"
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see the text "approved 1"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"
