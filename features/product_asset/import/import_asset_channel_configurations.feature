@javascript
Feature: Import asset channel configurations
  In order to use the assets
  As an admin
  I need to be able to import channel configuration to be able to apply transformations

  Scenario: Import and create channel configurations
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        -
            channel: ecommerce
            configuration:
                scale:
                    width: 200
                colorspace:
                    colorspace: gray
    """
    And the following job "apparel_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "apparel_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "apparel_asset_channel_configuration_import" job to finish
    Then I should see "read lines 1"
    And I should see "created 1"

  Scenario: Import and update channel configurations
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        -
            channel: mobile
            configuration:
                scale:
                    width: 200
                colorspace:
                    colorspace: gray
        -
            channel: mobile
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see "read lines 2"
    And I should see "processed 2"

  Scenario: Import asset file with missing required channel header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        -
            wrong: mobile
            configuration:
                scale:
                    width: 200
                colorspace:
                    colorspace: gray
        -
            wrong: tablet
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    And I should see "Field \"channel\" is expected, provided fields are \"wrong, configuration, code\""

  Scenario: Import asset with missing value for channel field
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        -
            channel:
            configuration:
                scale:
                    width: 200
                colorspace:
                    colorspace: gray
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see " Channel \"\" does not exists"

  Scenario: Import and update channel configurations with unknown configured transformation
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        -
            channel: mobile
            configuration:
                wrongTransformation:
                    width: 200
                colorspace:
                    colorspace: gray
        -
            channel: tablet
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see "read lines 2"
    And I should see "processed 1"
    And I should see "skipped 1"
    And I should see "Transformation \"wrongTransformation\" is unknown"

  Scenario: Import and update channel configurations with invalid transformation configuration
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        -
            channel: mobile
            configuration:
                scale:
                    wrongField: 200
                colorspace:
                    colorspace: gray
        -
            channel: tablet
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see "read lines 2"
    And I should see "processed 1"
    And I should see "skipped 1"
    And I should see "Transformation \"scale\" is not well configured"
    And I should see "Your options does not fulfil the requirements of the \"scale\" transformation."
