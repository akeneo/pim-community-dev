@javascript @proposal-feature-enabled
Feature: Display proposal widget
  In order to easily see which products have pending proposals
  As a product manager
  I need to be able to see a widget with pending proposals on the dashboard

  Scenario: Display proposal widget
    Given the "clothing" catalog configuration
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see the text "Proposals to review"
    And I should see the text "No proposals to review"

  Scenario: Successfully display all proposals that I can review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family  | categories |
      | my-jacket    | jackets | jackets    |
      | my-tee-shirt | tees    | tees       |
    And the following product drafts:
      | product      | source | source_label | author | author_label | status      | result                                                                    |
      | my-jacket    | pim    | PIM          | mary   | Mary Smith   | ready       | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
      | my-tee-shirt | pim    | PIM          | mary   | Mary Smith   | ready       | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change2"}]}} |
      | my-jacket    | pim    | PIM          | john   | John Doe     | in progress | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change3"}]}} |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see the text "Proposals to review"
    And I should see the following proposals on the widget:
      | product   | author     |
      | my-jacket | Mary Smith |

  Scenario: Successfully hide the widget if the current user is not the owner of any categories
    Given the "default" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should not see the text "Proposals to review"
