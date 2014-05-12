'use strict';

angular.module('App.services', [])
    .service('GridManager', function ($http, $q, $window) {
        var self = this;

        this.gridParams = {};

        var loadGridDataCanceler;

        this.initializeGridParams = function(name) {
            self.gridParams[name] = {
                dataLocale: 'en_US',
                params: {
                    dataLocale: 'en_US'
                }
            };
        };

        this.applyGridParams = function(name, params) {
            self.gridParams[name] = _.assign(
                self.gridParams[name],
                {
                    _pager: {
                        _page: params.state.currentPage,
                        _per_page: params.state.pageSize
                    },
                    _sort_by: params.state.sorters
                }
            );
        };

        this.applyFilter = function(name, filterConfig) {
            self.gridParams[name] = _.assign(self.gridParams[name], { _filter: filterConfig });
        };

        this.loadGrid = function (name) {
            self.initializeGridParams(name);
            var deferred = $q.defer();

            $http.get('/datagrid/' + name + '/load?' + $.param(self.gridParams[name])).then(function(resp) {
                deferred.resolve(self.prepareGridConfig(resp.data));
            });

            return deferred.promise;
        };

        this.loadGridData = function (name, params) {
            //we cancel the previous request
            if (loadGridDataCanceler) {
                loadGridDataCanceler.resolve();
            }

            loadGridDataCanceler = $q.defer();
            var deferred = $q.defer();

            self.applyGridParams(name, params);

            var urlParams = {};
            urlParams[name] = self.gridParams[name];

            var url = '/datagrid/' +
                name +
                '?' +
                $.param(urlParams);

            $http({method:'GET', url: url, timeout: loadGridDataCanceler.promise}).then(function(resp) {
                var data = {
                    metadata: params,
                    data: resp.data
                };

                deferred.resolve(self.prepareGridConfig(data));
            });

            return deferred.promise;
        };

        this.prepareGridConfig = function (config) {

            try {
                config.data = JSON.parse(config.data);
            } catch (e) {

            }

            var columns = _.pluck(config.metadata.columns, 'name');

            config.data.data = _.map(config.data.data, function (row) {
                return {
                    row: _.map(columns, function (column) {
                        return {
                            value: row[column],
                            column: column
                        };
                    }),
                    entity: row
                };
            });

            return config;
        };

        return {
            load: this.loadGrid,
            loadData: this.loadGridData,
            applyFilter: this.applyFilter
        };
    })
    .service('CellManager', function($sce, $filter) {
        this.renderCell = function(cell, column) {
            if (typeof column != 'undefined') {
                switch (column.type) {
                    case 'string':
                        return cell;
                    case 'date':
                        return $filter('date')(cell, 'mediumDate');
                    case 'html':
                        return $sce.trustAsHtml(cell);
                }
            }

            return cell;
        };

        return {
            render: this.renderCell
        };
    });
