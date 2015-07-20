@javascript
Feature: Upload and import variant groups with media
  In order to easily import existing variant group media
  As a product manager
  I need to be able to upload and import variant groups along with media

  Scenario: Successfully upload and import variant groups from an archive
    Given a "footwear" catalog configuration
    And the following attributes:
      | code       | type | label-en_US | allowed_extensions |
      | attachment | file | Attachment  | txt                |
    And I am logged in as "Julia"
    And I am on the "footwear_variant_group_import" import job page
    When I upload and import the file "caterpillar_variant_import.zip"
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "Created 1"
    And I should see "Processed 1"
    When I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    Then the field Name should contain "Very nice boots"
    When I visit the "Media" group
    Then I should see "akeneo.jpg"
    When I visit the "Other" group
    Then I should see "akeneo.txt"
