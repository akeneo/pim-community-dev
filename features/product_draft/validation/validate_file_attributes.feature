@info More user-friendly validation to be done in the scope of @jira https://akeneo.atlassian.net/browse/PIM-2029
Feature: Validate file attributes of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for file attributes

  Background:
    Given the "clothing" catalog configuration
    And the following attributes:
      | code       | label-en_US | type | scopable | max_file_size | allowed_extensions | group |
      | brief      | Brief       | file | no       | 0.01          | jpg                | info  |
      | attachment | Attachment  | file | yes      | 0.01          | jpg                | info  |
    And the following family:
      | code | label-en_US | attributes                 |
      | baz  | Baz         | sku, brief, attachment |
    And the following product:
      | sku | family | categories        |
      | foo | baz    | summer_collection |
    And I am logged in as "Mary"
    And I am on the "foo" product page

  Scenario: Validate the max file size constraint of file attribute
    Given I attach file "akeneo.jpg" to "Brief"
    And I save the product
    Then I should see "The file is too large (10.58 kB). Allowed maximum size is 10 kB."

  Scenario: Validate the max file size constraint of scopable file attribute
    Given I attach file "akeneo.jpg" to "Attachment"
    And I save the product
    Then I should see "The file is too large (10.58 kB). Allowed maximum size is 10 kB."

  Scenario: Validate the allowed extensions constraint of file attribute
    Given I attach file "fanatic-freewave-76.gif" to "Brief"
    And I save the product
    Then I should see "The file extension is not allowed (allowed extensions: jpg)."

  Scenario: Validate the allowed extensions constraint of scopable file attribute
    Given I attach file "fanatic-freewave-76.gif" to "Attachment"
    And I save the product
    Then I should see "The file extension is not allowed (allowed extensions: jpg)."
