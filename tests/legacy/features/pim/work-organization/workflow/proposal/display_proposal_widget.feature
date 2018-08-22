@javascript
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
      | product      | author | status      | result                                                                    |
      | my-jacket    | mary   | ready       | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
      | my-tee-shirt | mary   | ready       | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change2"}]}} |
      | my-jacket    | john   | in progress | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change3"}]}} |
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see the text "Proposals to review"
    And I should see the following proposals on the widget:
      | product   | author     |
      | my-jacket | Mary Smith |

  Scenario: Successfully display new proposals that I can review
    Given a "clothing" catalog configuration
    And the following product:
      | sku          | family | categories |
      | my-tee-shirt | tees   | tees       |
    And the following product drafts:
      | product      | author | status | result                                                                    |
      | my-tee-shirt | mary   | ready  | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
    And I am logged in as "Peter"
    When I edit the "my-tee-shirt" product
    And I visit the "Categories" column tab
    And I visit the "2014 collection" tab
    And I expand the "2014_collection" category
    And I expand the "summer_collection" category
    And I click on the "jackets" category
    And I save the product
    And I logout
    And I am logged in as "Julia"
    And I go to the dashboard page
    Then I should see the text "Proposals to review"
    And I should see the following proposals on the widget:
      | product      | author     |
      | my-tee-shirt | Mary Smith |

  Scenario: Successfully hide proposals belonging to the last category I was owner of that was removed
    Given a "clothing" catalog configuration
    And the following product:
      | sku       | family  | categories |
      | my-jacket | jackets | jackets    |
    And the following product drafts:
      | product   | author | status      | result                                                                    |
      | my-jacket | mary   | ready       | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change1"}]}} |
      | my-jacket | john   | in progress | {"values":{"name":[{"locale":"en_US","scope":null,"data":"My change2"}]}} |
    And I am logged in as "Peter"
    When I am on the "jackets" category page
    When I press the secondary action "Delete"
    And I confirm the deletion
    And I logout
    And I am logged in as "Julia"
    And I go to the dashboard page
    Then I should see the text "Proposals to review"
    And I should see the text "No proposals to review"

  Scenario: Successfully hide the widget if the current user is not the owner of any categories
    Given the "default" catalog configuration
    And I am logged in as "Sandra"
    When I am on the dashboard page
    Then I should not see "Proposals to review"
