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
                    $scope.data = JSON.parse(data.data);
                });
            }
        };
    })
    .directive('gridHeader', function() {
        return {
            restrict: 'A',
            scope: {
                metaData: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/header.html',
        };
    })
    .directive('gridRow', function() {
        return {
            restrict: 'A',
            scope: {
                row: '=',
                metaData: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/row.html',
            controller: function($scope) {
                $scope.getCellConfig = function (columnName) {
                    var columns = $scope.metaData.columns;

                    for (var i = columns.length - 1; i >= 0; i--) {
                        if (columns[i].name === columnName) {
                            return columns[i];
                        }
                    }
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
    });
