angular.module('App', ['ui.router', 'ui.bootstrap', 'App.services', 'App.controllers', 'App.directives', 'App.filters']).
config(function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/');
});
