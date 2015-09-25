@javascript
Feature: Export asset channel configurations
  In order to be able to access and modify asset channel configuration data outside PIM
  As an admin
  I need to be able to export channel configuration to be able to change the configuration

  Scenario: Successfully export asset channel configurations
    Given a "clothing" catalog configuration
    And the following job "clothing_asset_channel_configuration_export" configuration:
      | filePath | %tmp%/asset_channel_configurations_export/asset_channel_configurations.yml |
    And I am logged in as "Julia"
    And I am on the "clothing_asset_channel_configuration_export" export job page
    When I launch the export job
    And I wait for the "clothing_asset_channel_configuration_export" job to finish
    And I should see "read 2"
    And I should see "written 2"
    Then exported file of "clothing_asset_channel_configuration_export" should contain:
    """
    asset_channel_configurations:
        mobile:
            configuration:
                scale:
                    width: 200
                colorspace:
                    colorspace: gray
        tablet:
            configuration:
                resize:
                    width: 40
                    height: 50
    """
