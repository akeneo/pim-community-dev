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
      | product        | status | author                        | result                                                                                 |
      | leather-jacket | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}} |
      | wool-jacket    | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}}       |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Approve selected" button
    And I confirm the action
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see "approved 2"
    When I edit the "leather-jacket" product
    Then the product Name should be "Awesome leather jacket"
    When I edit the "wool-jacket" product
    Then the product Name should be "Lame wool jacket"

  Scenario: Unsuccessfully approve proposals that contain values I can't edit
    Given the following product drafts:
      | product        | status | author                        | result                                                                                |
      | leather-jacket | ready  | clothing_product_draft_import | {"values":{"legacy_attribute":[{"locale":"en_US","scope":null,"data":"Dumb value"}]}} |
      | wool-jacket    | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}}       |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Approve selected" button
    And I confirm the action
    And I wait for the "approve_product_draft" job to finish
    And I go on the last executed job resume of "approve_product_draft"
    Then I should see "approved 1"
    And I should see "Skipped 1"
    And I should see "You can't edit the attributes modified by this proposal"

  Scenario: Successfully refuse several proposals
    Given the following product drafts:
      | product        | status | author                        | result                                                                                |
      | leather-jacket | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Awesome leather jacket"}]}} |
      | wool-jacket    | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}}       |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Refuse selected" button
    And I confirm the action
    And I wait for the "refuse_product_draft" job to finish
    And I go on the last executed job resume of "refuse_product_draft"
    Then I should see "refused 2"
    When I edit the "leather-jacket" product
    Then the product Name should be "Leather jacket"
    When I edit the "wool-jacket" product
    Then the product Name should be "Wool jacket"

  Scenario: Unsuccessfully refuse proposals that contain values I can't edit
    Given the following product drafts:
      | product        | status | author                        | result                                                                                |
      | leather-jacket | ready  | clothing_product_draft_import | {"values":{"legacy_attribute":[{"locale":"en_US","scope":null,"data":"Dumb value"}]}} |
      | wool-jacket    | ready  | clothing_product_draft_import | {"values":{"name":[{"locale":"en_US","scope":null,"data":"Lame wool jacket"}]}}       |
    And I am on the proposals page
    When I select rows Leather jacket and Wool jacket
    And I press the "Refuse selected" button
    And I confirm the action
    And I wait for the "refuse_product_draft" job to finish
    And I go on the last executed job resume of "refuse_product_draft"
    Then I should see "refused 1"
    And I should see "Skipped 1"
    And I should see "You can't edit the attributes modified by this proposal"
