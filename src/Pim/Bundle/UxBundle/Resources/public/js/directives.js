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
    })
    .directive('gridRow', function() {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                row: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/row.html',
            controller: function($scope) {
            }
        };
    })
    .directive('gridCell', function() {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                cell: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/cell.html',
            controller: function($scope) {
            }
        };
    });
