'use strict';

angular.module('App.directives', [])
    .directive('grid', function() {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                name: '@'
            },
            templateUrl: '/bundles/pimux/templates/grid/grid.html',
            controller: function(GridManager, $scope) {
                GridManager.load($scope.name).then(function (data) {
                    $scope.metaData = data.metadata;
                    $scope.data = JSON.parse(data.data);
                });
            }
        };
    });
