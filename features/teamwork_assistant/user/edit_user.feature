@javascript
Feature: Update the use information
  In order to set a default view to user
  As an administrator
  I need to only select a public view

  Background:
    Given the "teamwork_assistant" catalog configuration
    And the following datagrid views:
      | label   | alias        | columns | filters   |
      | My view | product-grid | sku     | f[sku]=-1 |
    And the following projects:
      | label                  | owner | due_date   | description                                  | channel   | locale | product_filters |
      | Collection Summer 2030 | julia | 2030-10-28 | Please do your best to finish before Summer. | ecommerce | en_US  | []              |
      | Collection Winter 2030 | julia | 2030-08-28 | Please do your best to finish before Winter. | mobile    | en_US  | []              |
    And I am logged in as "Julia"

  Scenario: Project views are displayable
    Given I edit the "admin" user
    When I visit the "Additional" tab
    Then I should not see the choices collection-summer-2030-ecommerce-en-us in Default product grid view
    And I should not see the choices collection-winter-2030-mobile-en-us in Default product grid view
    But I should see the choices My view in Default product grid view
