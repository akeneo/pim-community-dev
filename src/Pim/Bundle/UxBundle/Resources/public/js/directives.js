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

                $scope.$on('grid.page_size.changed', function (event, pageSize) {
                    $scope.metaData.state.pageSize = pageSize;

                    $scope.$emit('grid.need.reload');
                });

                $scope.$on('grid.current_page.changed', function (event, currentPage) {
                    $scope.metaData.state.currentPage = currentPage;

                    $scope.$emit('grid.need.reload');
                });

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
                totalRecords: '='
            },
            templateUrl: '/bundles/pimux/templates/grid/pagination.html',
            controller: function($scope) {
                //We initialize the state of the pagination directive
                $scope.state = {
                    currentPage: $scope.currentPage,
                    pageSize: $scope.pageSize,
                    lastPage: Math.round($scope.totalRecords / $scope.pageSize)
                };

                $scope.$watch('state.currentPage', function (newValue, oldValue) {
                    if (oldValue && newValue != oldValue) {
                        if ($scope.state.currentPage < 1) {
                            $scope.state.currentPage = 1;
                        } else if ($scope.state.currentPage > $scope.state.lastPage) {
                            $scope.state.currentPage = $scope.state.lastPage;
                        }

                        $rootScope.$broadcast('grid.current_page.changed', $scope.state.currentPage);
                    }
                }, true);

                $scope.$watch('state.pageSize', function (newValue, oldValue) {
                    if (oldValue && newValue != oldValue) {
                        $scope.state.lastPage = Math.round($scope.totalRecords / $scope.state.pageSize);

                        $scope.state.currentPage = 1;

                        $rootScope.$broadcast('grid.page_size.changed', $scope.state.pageSize);
                    }
                }, true);

                $scope.$on('grid.page_size.changed', function (event, pageSize) {
                    if (pageSize != $scope.state.pageSize) {
                        $scope.state.pageSize = pageSize;
                    }
                });

                $scope.$on('grid.current_page.changed', function (event, currentPage) {
                    if (currentPage != $scope.state.currentPage) {
                        $scope.state.currentPage = currentPage;
                    }
                });
            }
        };
    })
    .directive('gridSelection', function() {
        return {
            templateUrl: '/bundles/pimux/templates/grid/selection.html'
        };
    });
