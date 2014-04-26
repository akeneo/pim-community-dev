'use strict';

angular.module('App.services', [])
    .service('GridManager', function ($http, $q) {
        var self = this;

        this.loadGrid = function (name) {
            var deferred = $q.defer();

            $http.get('/datagrid/' + name + '/load').then(function(resp) {
                deferred.resolve(self.prepareGridConfig(resp.data));
            });

            return deferred.promise;
        };

        this.prepareGridConfig = function (config) {
            config.data = JSON.parse(config.data);

            var columns = _.pluck(config.metadata.columns, 'name');

            config.data.data = _.map(config.data.data, function (row) {
                return _.map(columns, function (column) {
                    return {
                        value: row[column],
                        column: column
                    };
                });
            });

            return config;
        };

        return {
            load: this.loadGrid
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
