@javascript
Feature: Allow only XHR requests for some proposals actions
  In order to protect proposals from CSRF attacks
  As a developer
  I need to only do XHR calls for some proposals actions

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | jackets       |
      | categories | winter_top    |
      | sku        | my-jacket     |
      | price      | 45 USD,75 EUR |

  Scenario: Authorize only XHR calls for proposals acceptance
    Given Mary proposed the following change to "my-jacket":
      | field | value       | tab                 |
      | SKU   | your-jacket | Product information |
    When I make a direct authenticated POST call to accept the last proposal of user "Mary"
    Then I should get the following proposals:
      | product   | username | result                                                                                                                                                                                          |
      | my-jacket | Mary     | {"values": {"sku": [{"locale": null, "scope": null, "data": "your-jacket"}]}, "review_statuses": {"sku": [{"locale": null, "scope": null, "status": "to_review"}]}} |

  Scenario: Authorize only XHR calls for proposals rejectance
    Given Mary proposed the following change to "my-jacket":
      | field | value       | tab                 |
      | SKU   | your-jacket | Product information |
    When I make a direct authenticated POST call to reject the last proposal of user "Mary"
    Then I should get the following proposals:
      | product   | username | result                                                                                                                                                                                          |
      | my-jacket | Mary     | {"values": {"sku": [{"locale": null, "scope": null, "data": "your-jacket"}]}, "review_statuses": {"sku": [{"locale": null, "scope": null, "status": "to_review"}]}} |

  Scenario: Authorize only XHR calls for proposals deletion
    Given Mary started to propose the following change to "my-jacket":
      | field | value       | tab                 |
      | SKU   | your-jacket | Product information |
    When I make a direct authenticated POST call to remove the last proposal of user "Mary"
    Then I should get the following proposals:
      | product   | username | result                                                                                                                                                                                          |
      | my-jacket | Mary     | {"values": {"sku": [{"locale": null, "scope": null, "data": "your-jacket"}]}, "review_statuses": {"sku": [{"locale": null, "scope": null, "status": "draft"}]}} |
