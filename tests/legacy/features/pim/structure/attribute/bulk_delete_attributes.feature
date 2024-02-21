@javascript
Feature: Bulk delete attributes
  As a product manager
  I need to be able to bulk delete existing attributes

  Scenario: Successfully bulk delete attributes
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    When I am on the attributes page
    When I select rows Rating, Manufacturer and Description
    And I press the "Delete" button in the bulk actions panel
    And I fill the input labelled 'Please type "delete"' with 'delete'
    And I press the "Delete" button
    And I wait for the "delete_attributes" job to finish
    And I am on the attributes page
    And I should not see attributes Rating, Manufacturer and Description
    And I should have 1 new notification
    And I should see notification:
      | type    | message                                     |
      | success | Deletion Bulk delete of attributes finished |
    When I go on the last executed job resume of "delete_attributes"
    Then I should see the text "COMPLETED"
    And I should see the text "Deleted attributes 3"
