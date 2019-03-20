@javascript
Feature: Import asset channel configurations
  In order to use the assets
  As an admin
  I need to be able to import channel configuration to be able to apply transformations

  @critical
  Scenario: Import and create channel configurations
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        ecommerce:
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
    Then I should see the text "read lines 1"
    And I should see the text "created 1"

  Scenario: Import and update channel configurations
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        mobile:
            configuration:
                scale:
                    width: 200
                colorspace:
                    colorspace: gray
        tablet:
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see the text "read lines 2"
    And I should see the text "processed 2"

  Scenario: Import asset file with wrong channel header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        wrong:
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
    And I should see the text "Channel \"wrong\" does not exist"

  Scenario: Import and update channel configurations with unknown configured transformation
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        mobile:
            configuration:
                wrongTransformation:
                    width: 200
                colorspace:
                    colorspace: gray
        tablet:
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see the text "read lines 2"
    And I should see the text "processed 1"
    And I should see the text "skipped 1"
    And I should see the text "Transformation \"wrongTransformation\" is unknown"

  Scenario: Import and update channel configurations with invalid transformation configuration
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following yaml file to import:
    """
    asset_channel_configuration:
        mobile:
            configuration:
                scale:
                    wrongField: 200
                colorspace:
                    colorspace: gray
        tablet:
            configuration:
                scale:
                    ratio: 25
    """
    And the following job "clothing_asset_channel_configuration_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_channel_configuration_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_channel_configuration_import" job to finish
    Then I should see the text "read lines 2"
    And I should see the text "processed 1"
    And I should see the text "skipped 1"
    And I should see the text "Transformation \"scale\" is not well configured"
    And I should see the text "Your options does not fulfil the requirements of the \"scale\" transformation."
