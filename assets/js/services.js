'use strict';

// Services /

var app = angular.module('myApp.services', ['ngRoute']);

app.factory('commonServices',function($http,$q,$window){
    var callAction = function (requestUrl, toPost) {
        //console.log(toPost);
        var deferred = $q.defer();
        $http({method: 'POST', url: 'apischoolapp/api.php',
            params: {'action' : requestUrl, 'is_post' : 'post'},
            data: { 'postData': toPost}
        }).success(function(data){
            if(data.is_success != 'false') {
                deferred.resolve(data);
            }
            else
            {
                $window.location = '/';
            }
        }).error(function(){
            console.log('network error');
        });
        return deferred.promise;
    };

    return {
        callAction: callAction
    };
});