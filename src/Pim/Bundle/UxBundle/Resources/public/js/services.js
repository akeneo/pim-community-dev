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
    });
