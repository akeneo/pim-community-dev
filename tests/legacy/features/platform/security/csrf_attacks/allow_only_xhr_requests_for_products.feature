Feature: Allow only XHR requests for some products actions
  In order to protect products from CSRF attacks
  As a developer
  I need to only do XHR calls for some products actions

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku  | comment         |
      | test | This is a test. |

  Scenario: Authorize only XHR calls for products creation
    When I make a direct authenticated POST call to create a "csrf" product in the family "boots"
    Then there should not be a "csrf" product

  Scenario: Authorize only XHR calls for products update
    When I make a direct authenticated POST call to disable the "test" product
    Then product "test" should be enabled

  Scenario: Authorize only XHR calls for products deletion
    When I make a direct authenticated DELETE call on the "test" product
    Then there should be a "test" product

  Scenario: Authorize only XHR calls for products attribute deletion
    When I make a direct authenticated DELETE call on the "test" product to remove the "comment" attribute
    Then the product "test" should have the following values:
      | comment   | This is a test. |
