Feature: Select a list of asset to put in an asset collection
    In order to update my asset collection
    As a user
    I want to be able to select & pick the assets for my collection

    @acceptance-front
    Scenario: Display the asset picker and pick some assets
        Given an asset collection with two assets
        And the user go to the asset tab
        And the user opens the asset picker
        When the user filters the assets
        And the user picks one assets
# Then the three assets in the collection should be displayed
