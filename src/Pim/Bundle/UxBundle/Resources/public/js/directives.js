'use strict';

angular.module('App.directives', [])
    .directive('grid', function() {
        return {
            restrict: 'A',
            scope: {
                name: '@'
            },
            templateUrl: '/bundles/pimux/templates/grid/grid.html',
            controller: function(GridManager, $scope) {
                GridManager.load($scope.name).then(function (data) {
                    $scope.metaData = data.metadata;
                    $scope.data     = data.data;
                });
            }
        };
    })
    .directive('gridHeader', function() {
        return {
            restrict: 'A',
            templateUrl: '/bundles/pimux/templates/grid/header.html',
        };
    })
    .directive('gridRow', function() {
        return {
            restrict: 'A',
            templateUrl: '/bundles/pimux/templates/grid/row.html',
            controller: function($scope) {
                $scope.getCellConfig = function (columnName) {
                    return _.find($scope.metaData.columns, {name: columnName});
                };

                $scope.performRowAction = function (action) {

                };
            }
        };
    })
    .directive('gridCell', function(CellManager) {
        return {
            restrict: 'A',
            scope: {
                cell: '=',
                column: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/cell.html',
            controller: function($scope) {
                $scope.renderCell = function (cell, column) {
                    return CellManager.render(cell, column);
                };
            }
        };
    })
    .directive('gridFilters', function() {
        return {
            templateUrl: '/bundles/pimux/templates/grid/filters.html'
        };
    });
