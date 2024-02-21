@javascript
Feature: Leave a comment on a product
  In order to discuss about a product with my team mates
  As a product manager or a contributor
  I need to be able to leave a comment on the product

  Background:
    Given the "footwear" catalog configuration
    And the following product:
      | sku        |
      | rangers    |
      | high-heels |
    And the following product comments:
      | product    | # | author | message                                                        | parent | created_at |
      | high-heels | 1 | Mary   | The price is outdated.                                         |        | 27-Aug-14  |
      | high-heels | 2 | Julia  | Waiting for the confirmation of our manufacturer to update it. | 1      | 29-Aug-14  |
      | high-heels | 3 | Mary   | Ok, thanks Jul's.                                              | 1      | 01-Sep-14  |
      | high-heels | 4 | Julia  | Does not belong to the Summer Collection anymore.              |        | 25-Aug-14  |
      | high-heels | 5 | Mary   | Should be associated with red heel.                            |        | 28-Aug-14  |

  Scenario: Successfully add a new comment on a product
    Given I am logged in as "Julia"
    And I am on the "rangers" product page
    And I visit the "Comments" column tab
    Then I should see the text "No comment for now"
    When I add a new comment "My comment"
    Then I should not see the text "No comment for now"
    And I should see the following product comments:
      | product | # | author | message    | parent |
      | rangers | 1 | Julia  | My comment |        |

  Scenario: View the list of comments on a product
    Given I am logged in as "Julia"
    And I am on the "high-heels" product page
    And I visit the "Comments" column tab
    Then I should see the following product comments:
      | product    | # | author | message                                                        | parent |
      | high-heels | 1 | Mary   | The price is outdated.                                         |        |
      | high-heels | 2 | Julia  | Waiting for the confirmation of our manufacturer to update it. | 1      |
      | high-heels | 3 | Mary   | Ok, thanks Jul's.                                              | 1      |
      | high-heels | 4 | Julia  | Does not belong to the Summer Collection anymore.              |        |
      | high-heels | 5 | Mary   | Should be associated with red heel.                            |        |

  Scenario: Successfully reply to an existing comment
    Given I am logged in as "Julia"
    And I am on the "high-heels" product page
    And I visit the "Comments" column tab
    When I reply to the comment "Should be associated with red heel." of "Mary" with "No, with black heels."
    Then I should see the following product comments:
      | product    | # | author | message                                                        | parent |
      | high-heels | 1 | Mary   | The price is outdated.                                         |        |
      | high-heels | 2 | Julia  | Waiting for the confirmation of our manufacturer to update it. | 1      |
      | high-heels | 3 | Mary   | Ok, thanks Jul's.                                              | 1      |
      | high-heels | 4 | Julia  | Does not belong to the Summer Collection anymore.              |        |
      | high-heels | 5 | Mary   | Should be associated with red heel.                            |        |
      | high-heels | 6 | Julia  | No, with black heels.                                          | 5      |

  Scenario: Successfully remove my own comments
    Given I am logged in as "Julia"
    And I am on the "rangers" product page
    And I visit the "Comments" column tab
    And I add a new comment "My comment"
    When I delete the "My comment" comment
    Then I should see the text "Confirm deletion"
    And I confirm the removal
    Then I should not see the text "My comment"

  Scenario: Not being able to remove a comment that is not mine
    Given I am logged in as "Julia"
    And I am on the "high-heels" product page
    And I visit the "Comments" column tab
    Then I should not see the link to delete the "Should be associated with red heel." comment of "Mary"

  Scenario: Not being able to view comments if I don't have the permission
    Given I am logged in as "Peter"
    And I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Comment products
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "rangers" product page
    Then I should not see the text "Comments"
