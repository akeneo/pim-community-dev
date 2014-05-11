'use strict';

angular.module('App.directives', [])
    .directive('grid', function() {
        return {
            scope: {
                name: '@'
            },
            templateUrl: '/bundles/pimux/templates/grid/grid.html',
            controller: function(GridManager, $scope) {
                GridManager.load($scope.name).then(function (data) {
                    $scope.metaData = data.metadata;
                    $scope.data     = data.data;
                });

                $scope.applyFilter = function(filterName, value) {
                    var config = {};
                    config[filterName] = value;

                    GridManager.applyFilter($scope.name, config);
                    $scope.$broadcast('grid.need.reload');
                };

                $scope.$on('grid.need.reload', function (event) {
                    GridManager.loadData($scope.name, $scope.metaData).then(function (data) {
                        $scope.data = data.data;
                    });
                });
            }
        };
    })
    .directive('gridHeader', function() {
        return {
            templateUrl: '/bundles/pimux/templates/grid/header.html',
        };
    })
    .directive('gridRow', function() {
        return {
            templateUrl: '/bundles/pimux/templates/grid/row.html',
            controller: function($scope) {
                $scope.getCellConfig = function (columnName) {
                    return _.find($scope.metaData.columns, {name: columnName});
                };
            }
        };
    })
    .directive('gridCell', function(CellManager) {
        return {
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
    })
    .directive('gridFilter', function() {
        return {
            template: '<div ng-include="\'/bundles/pimux/templates/grid/filter/\' + filter.type + \'.html\'"></div>'
        };
    })
    .directive('gridPagination', function($rootScope) {
        return {
            scope: {
                currentPage: '=',
                pageSize: '=',
                pageSizeOptions: '=',
                totalRecords: '=',
                metaData: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/pagination.html',
            controller: function($scope) {
                $scope.lastPage = Math.round($scope.totalRecords / $scope.pageSize);

                $scope.$watch('currentPage', function (newValue, oldValue) {
                    if (oldValue) {
                        if ($scope.currentPage < 0) {
                            $scope.currentPage = 0;
                        } else if ($scope.currentPage > $scope.lastPage) {
                            $scope.currentPage = $scope.lastPage;
                        }

                        //This should be asked by the grid manager
                        $scope.metaData.state.currentPage = $scope.currentPage;
                        $rootScope.$broadcast('grid.need.reload');
                    }
                }, true);

                $scope.$watch('pageSize', function (newValue, oldValue) {
                    if (oldValue) {
                        $scope.lastPage = Math.round($scope.totalRecords / $scope.pageSize);

                        //This will trigger the "watch" event on current page and ask for a second reload
                        //We should handle that with http request aborting.
                        $scope.currentPage = 1;

                        //This should be asked by the grid manager
                        $scope.metaData.state.pageSize = $scope.pageSize;
                        $rootScope.$broadcast('grid.need.reload');
                    }
                }, true);
            }
        };
    })
    .directive('gridSelection', function() {
        return {
            templateUrl: '/bundles/pimux/templates/grid/selection.html'
        };
    });
