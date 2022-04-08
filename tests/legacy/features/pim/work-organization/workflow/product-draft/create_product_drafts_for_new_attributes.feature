@javascript @proposal-feature-enabled @jira https://akeneo.atlassian.net/browse/PIM-3331
Feature: Create product drafts for new attributes added to the product
  In order to be able to propose changes to product data for new attributes
  As a redactor
  I need to be able to propose changes to a newly added attribute to the product's family

  Scenario: Create product draft for a new attribute of the family
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family | categories        |
      | tshirt | tees   | summer_collection |
    And I am logged in as "Mary"
    Given I am on the "tees" family page
    And I visit the "Attributes" tab
    And I add available attribute Comment
    And I save the family
    And I should not see the text "There are unsaved changes"
    When I am on the "tshirt" product page
    And I visit the "Other" group
    And I change the Comment to "tshirt"
    And I save the product
    Then I should see the text "Send for approval"
