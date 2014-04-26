'use strict';

angular.module('App.services', [])
    .service('GridManager', function ($http, $q) {
        this.loadGrid = function (name) {
            var deferred = $q.defer();

            $http.get('/datagrid/' + name + '/load').then(function(resp) {
                deferred.resolve(resp.data);
            });

            return deferred.promise;
        };

        return {
            load: this.loadGrid
        };
    })
    .service('CellManager', function($sce) {
        this.renderCell = function(cell, column) {
            if (typeof column != 'undefined') {
                switch (column.type) {
                    case 'string':
                        return cell;
                    break;
                    case 'date':
                        //for example
                        return (new Date(cell)).toDateString();
                    break;
                    case 'html':
                        return $sce.trustAsHtml(cell);
                    break;
                }
            }

            return cell;
        };

        return {
            render: this.renderCell
        };
    });
