'use strict';
// Here we set up an angular module. We'll attach controllers and
// other components to this module.
var app = angular.module('myApp', ['myApp.services','myApp.controllers', "ngRoute"])
    .run(function($rootScope, $routeParams, $location){
      $rootScope.apiUrl = 'http://api.schoolapp';
      if($location.path() == '/add_information' || $location.path() == '/notification' || $location.path() == '/add_news') {
          $rootScope.active_tab = 'content_all';
      } else {
          $rootScope.active_tab = '';
      }
      
    })
    .config(function ( $routeProvider) {
        $routeProvider
            .when('/', {
                templateUrl: 'partial/inner_student.html',
                controller: 'ManageUsers'
            })
            .when('/dashboard', {
                templateUrl: 'partial/student.html',
                controller: 'LoginController'
            })
            .when('/add_school', {
                templateUrl: 'partial/add_school.html',
                controller: 'ManageUsers'
            })
            .when('/add_parent', {
                templateUrl: 'partial/add_parent.html',
                controller: 'ManageUsers'
            })
            .when('/list_schools', {
                templateUrl: 'partial/list_schools.html',
                controller: 'ManageUsers'
            })
            .when('/list_parents', {
                templateUrl: 'partial/list_parents.html',
                controller: 'ManageUsers'
            })
            .when('/users', {
                templateUrl: 'partial/list_users.html',
                controller: 'ManageUsers',
                
            })
             .when('/edit_users/:user_id/:user_type', {
                templateUrl: 'partial/inner_student.html',
             })
             .when('/edit_parent/:user_id/:user_type', {
                templateUrl: 'partial/add_parent.html',
             })
             .when('/edit_school/:user_id/:user_type', {
                templateUrl: 'partial/add_school.html',
                controller: 'ManageUsers',
             })
            .when('/users_teachers', {
                templateUrl: 'partial/list_users_teacher.html',
                controller: 'ManageUsers',
                
            })
            .when('/add_news', {
                templateUrl: 'partial/add_news.html',
                controller: 'ManageContent',
            })
            .when('/add_news_class', {
                templateUrl: 'partial/add_news_class.html',
                controller: 'ManageContent',
            })
             .when('/list_news_class', {
                templateUrl: 'partial/list_news_class.html',
                controller: 'ManageContent',
            })
            .when('/list_info_class', {
                templateUrl: 'partial/list_info_class.html',
                controller: 'ManageContent',
            })
            .when('/add_assignment', {
                templateUrl: 'partial/add_assignment.html',
                controller: 'ManageContent',
            })
            .when('/add_info_class', {
                templateUrl: 'partial/add_info_class.html',
                controller: 'ManageContent',
            })
             .when('/add_timetable', {
                templateUrl: 'partial/add_timetable.html',
                controller: 'ManageContent',
            })
            .when('/list_timetable', {
                templateUrl: 'partial/list_timetable.html',
                controller: 'ManageContent',
            })
            .when('/list_assignment', {
                templateUrl: 'partial/list_assignment.html',
                controller: 'ManageContent',
            })
            .when('/edit_assignment/:content_id/:content_type', {
                templateUrl: 'partial/add_assignment.html',
                controller: 'ManageContent',
            })
             .when('/edit_timetable/:content_id/:content_type', {
                templateUrl: 'partial/add_timetable.html',
                controller: 'ManageContent',
            })
            .when('/edit_info_class/:content_id/:content_type', {
                templateUrl: 'partial/add_info_class.html',
                controller: 'ManageContent',
            })
            .when('/edit_news_class/:content_id/:content_type', {
                templateUrl: 'partial/add_news_class.html',
                controller: 'ManageContent',
            })
            .when('/add_information', {
                templateUrl: 'partial/add_information.html',
                controller: 'ManageContent',
            })
            .when('/add_notification', {
                templateUrl: 'partial/add_notification.html',
                controller: 'ManageContent',
            })
            .when('/list_news', {
                templateUrl: 'partial/list_news.html',
                controller: 'ManageContent',
            })
            .when('/list_info', {
                templateUrl: 'partial/list_info.html',
                controller: 'ManageContent',
            })
            .when('/list_notification', {
                templateUrl: 'partial/list_notification.html',
                controller: 'ManageContent',
            })
            .when('/edit_news/:content_id/:content_type', {
                templateUrl: 'partial/add_news.html',
                controller: 'ManageContent',
            })
            .when('/edit_notification/:content_id/:content_type', {
                templateUrl: 'partial/add_notification.html',
                controller: 'ManageContent',
            })
            .when('/edit_info/:content_id/:content_type', {
                templateUrl: 'partial/add_information.html',
                controller: 'ManageContent',
            })
            .otherwise({
                redirectTo: '/'
            });
    });

app.run(['$location', '$rootScope', function($location, $rootScope) {
        
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        //$rootScope.title = current.$$route.title;
    });
}]);