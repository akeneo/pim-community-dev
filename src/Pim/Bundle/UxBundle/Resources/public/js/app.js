'use strict';

angular.module('App', ['ngSanitize', 'ui.router', 'ui.bootstrap', 'App.services', 'App.controllers', 'App.directives', 'App.filters']).
config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/');

    $stateProvider
        .state('dashboard', {
            url: '/',
            template: '<h3>Dashboard</h3>'
        })
        .state('pim_enrich_product', {
            url: '/products',
            templateUrl: '/bundles/pimux/templates/product/index.html'
        });
});
