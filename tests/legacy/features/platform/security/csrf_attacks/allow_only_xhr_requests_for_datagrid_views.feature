Feature: Allow only XHR requests for some datagrid views actions
  In order to protect datagrid views from CSRF attacks
  As a developer
  I need to only do XHR calls for some datagrid views actions

  Background:
    Given a "default" catalog configuration
    And the following datagrid views:
      | label     | alias        | columns | filters   |
      | Sku views | product-grid | sku     | f[sku]=-1 |

  Scenario: Authorize only XHR calls for datagrid views deletion
    When I make a direct authenticated DELETE call on the "Sku views" datagrid view as "Peter"
    Then there should be a "Sku views" datagrid view
