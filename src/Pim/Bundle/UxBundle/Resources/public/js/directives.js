'use strict';

angular.module('App.directives', [])
    .directive('grid', function() {
        return {
            restrict: 'E',
            replace: true,
            scope: {
                name: '@'
            },
            template: '<div><p>This is "{{ name }}"</p><p>{{ gridData }}</p></div>',
            controller: function(GridManager, $scope) {
                GridManager.load($scope.name).then(function (data) {
                    $scope.gridData = data;
                });
            }
        };
    });
