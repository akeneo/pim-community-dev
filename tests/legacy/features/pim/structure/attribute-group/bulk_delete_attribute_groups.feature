@javascript
Feature: Bulk delete attribute groups
  As a product manager
  I need to be able to bulk delete existing attribute groups

  Scenario: Successfully bulk delete attribute groups
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute groups page
    When I select rows Sizes, Colors and Other
    And I press the "Delete" button
    And I fill the input labelled 'Please type "delete"' with 'delete'
    And I press the "Delete" button in the popin
    And I wait for the "delete_attribute_groups" job to finish
    And I am on the attribute groups page
    And I should not see the text "Sizes"
    And I should not see the text "Colors"
    And I should see the text "Other"
    And I should have 1 new notification
    And I should see notification:
      | type    | message                                                              |
      | warning | Deletion Bulk delete of attribute groups finished with some warnings |
    When I go on the last executed job resume of "delete_attribute_groups"
    Then I should see the text "COMPLETED"
    And I should see the text "Deleted attribute groups 2"
