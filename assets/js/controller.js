var myApp = angular.module('myApp.controllers', ['ngRoute', 'myApp.services', 'ui.bootstrap']);

myApp.controller('LoginController', ['$scope', 'commonServices', '$location', '$window', '$routeParams', function ($scope, commonServices, $location, $window, $routeParams) {
        $scope.authLogin = function () {
            var toPost = {};
            var class_str = '';
            toPost.username = $scope.username;
            toPost.password = $scope.password;
            toPost.device = 'desktop';
            toPost.token = '';
            toPost.platform = '';

            commonServices.callAction('login', toPost).then(function (res) {
                if (res.status == 'Ok' && res.is_success) {
                    //$location.path('/dashboard');
                    $window.location.href = 'dashboard.html';
                } else {
                    console.log(res.msg);
                    $scope.msg = res.msg;
                }
            });
        }
}]);

myApp.controller('ManageUsers', ['$scope', 'commonServices', '$location', '$window', '$routeParams','$filter', function ($scope, commonServices, $location, $window, $routeParams,$filter) {
        $scope.user_type = $routeParams['user_type'];
        $scope.user_id = $routeParams['user_id']
        
        $scope.Items = [{
                Name: [{label: "Class 1", value: "1"}, {label: "Class 2", value: "2"}]
            }, {
                Name: [{label: "Class 3", value: "3"}, {label: "Class 4", value: "4"}]
            }, {
                Name: [{label: "Class 5", value: "5"}, {label: "Class 6", value: "6"}]
            }, {
                Name: [{label: "Class 7", value: "7"}, {label: "Class 8", value: "8"}]
            }, {
                Name: [{label: "Class 9", value: "9"}, {label: "Class 10", value: "10"}]
            }];
        $scope.Items1 = [
            {label: "Class 1", value: "1"},
            {label: "Class 2", value: "2"},
            {label: "Class 3", value: "3"}, {label: "Class 4", value: "4"}
            , {label: "Class 5", value: "5"}, {label: "Class 6", value: "6"}
            , {label: "Class 7", value: "7"}, {label: "Class 8", value: "8"}
            , {label: "Class 9", value: "9"}, {label: "Class 10", value: "10"}
        ]
        
        $scope.createUser = function (user_type) {
            $scope.loader_show = true;
            $scope.loader_hide = false;
            //console.log(user_type);
            var toPost = {};
            var class_str = '';
            toPost.user_type = user_type;
            toPost.device = 'desktop';
            toPost.token = '';
            toPost.platform = '';

            if (user_type == '2') {
                toPost.name = $scope.name_teacher;
                toPost.email = $scope.email_teacher;
                var class_str = '';
                angular.forEach($scope.Items, function (item) {
                    angular.forEach(item.Name, function (item) {
                        // console.log(item.value);
                        if (item.value == true) {
                            var class_name = item.label.split(' ');
                            class_str += class_name[1] + ',';
                        }
                    });
                });
            } else
            {
                var class_str = $scope.student_class;
                toPost.name = $scope.name_student;
                 toPost.email = $scope.email_student;
                //alert(class_str);
            }

            
            toPost.class_assoc = class_str;
            if($scope.user_id_teacher && user_type == 2) {
                toPost.user_id = $scope.user_id_teacher;
            }
            else if($scope.user_id_student && user_type == 1)
            {
                 toPost.user_id = $scope.user_id_student;
            }
            commonServices.callAction('create_user', toPost).then(function (res) {

                if (res.status == 'Ok') {
                    if (res.is_success) {
                        $scope.name = '';
                        $scope.email = '';
                    }
                } else
                {
                    alert('Service not available');
                }
                $scope.loader_show = false;
                $scope.loader_hide = true;
                if (user_type == 2) {
                    $scope.msg_teach = res.msg;
                    $scope.msg_student = '';
                } else
                {
                    $scope.msg_student = res.msg;
                    $scope.msg_teach = '';
                }

                // console.log(res.status);
                // $location.path('/dashboard');
            });

        }
        
        $scope.deleteUser = function (user_id, index) {
            $scope.loader_show = true;
            $scope.loader_hide = false;
            var toPost = {};
            toPost.user_id = user_id;
            toPost.device = 'desktop';
            toPost.token = '';
            toPost.platform = '';
            commonServices.callAction('delete_user', toPost).then(function (res) {
                if (res.status == 'Ok' && res.is_success) {
                   $scope.lists.splice(index, 1);
                } else
                {
                    alert('Service not available');
                }
                $scope.loader_show = false;
                $scope.loader_hide = true;
                
                $scope.msg = res.msg;
                
            });

        }

        $scope.listUsers = function (user_type) {
            //console.log(user_type);
            var toPost = {};
            toPost.user_type = user_type;
            toPost.device = 'desktop';
            toPost.token = '';
            commonServices.callAction('list_users', toPost).then(function (res) {
                if (res.status == 'Ok') {
                    //  console.log(res);
                    $scope.lists = res.data;
                    $scope.viewby = 5;
                    $scope.totalItems = $scope.lists.length;
                    $scope.currentPage = 1;
                    $scope.itemsPerPage = $scope.viewby;
                    $scope.maxSize = 10; //Number of pager buttons to show
                } else
                {
                    alert('Error occured during apicall');
                }
                $scope.msg = res.msg;
                // console.log(res.status);
                // $location.path('/dashboard');
            });

        }

        $scope.checkAll = function () {
            if ($scope.selectedAll) {
                $scope.selectedAll = true;
            } else {
                $scope.selectedAll = false;
            }
            // var i;
            // i = 1;
            angular.forEach($scope.Items, function (items) {
                angular.forEach(items.Name, function (item) {
                    item.value = $scope.selectedAll;
                });
            });


        };

        $scope.setPage = function (pageNo) {
            $scope.currentPage = pageNo;
        };

        $scope.pageChanged = function () {
          //  console.log('Page changed to: ' + $scope.currentPage);
        };

        $scope.setItemsPerPage = function (num) {
            $scope.itemsPerPage = num;
            $scope.currentPage = 1; //reset to first paghe
        }

        $scope.search_user = function (user_type) {
            var toPost = {};
            // console.log('hi'+$scope.search_txt);
            toPost.search_txt = $scope.search_txt;
            toPost.user_type = user_type;
            toPost.device = 'desktop';
            toPost.token = '';
            commonServices.callAction('search_user', toPost).then(function (res) {
                if (res.status == 'Ok') {
                    $scope.lists = res.data;
                    $scope.viewby = 5;
                    $scope.totalItems = $scope.lists.length;
                    $scope.currentPage = 1;
                    $scope.itemsPerPage = $scope.viewby;
                    $scope.maxSize = 10; //Number of pager buttons to show
                } else
                {
                    alert('Service not available');
                }
                $scope.msg = res.msg;
                // console.log(res.status);
                // $location.path('/dashboard');
            });
        }
        
        $scope.get_user = function (user_id, user_type) {
            //console.log(user_type);
            var toPost = {};
            toPost.device = 'desktop';
            toPost.token = '';
            toPost.user_id = user_id;
            commonServices.callAction('get_user_info', toPost).then(function (res) {
                console.log(res );
                if (res.status == 'Ok' && res.is_success) {
                      console.log(res.data[0].name);
                   if(user_type == 2) {
                       $scope.name_teacher = res.data[0].name;
                       $scope.user_id_teacher = res.data[0].user_id;
                       $scope.email_teacher = res.data[0].email;
                       $scope.disabled_teacher = true;
                       var arr_classes = res.data[0].class_assoc.split(',');
                      // console.log(arr_classes);
                       angular.forEach($scope.Items, function (items) {
                            angular.forEach(items.Name, function (item) {
                                 var class_name = item.label.split(' ');
                                 //console.log(class_name);
                                if($filter('filter')(arr_classes, class_name[1]).length) {
                                    console.log($filter('filter')(arr_classes, class_name[1]));
                                  item.value = true;  
                                }
                                //if(arr_classes, item.value)
                                //item.value = $scope.selectedAll;
                            });
                       });
                   }
                   else {
                       $scope.name_student = res.data[0].name;
                       $scope.user_id_student = res.data[0].user_id;
                       $scope.email_student = res.data[0].email;
                       $scope.disabled_student = true;
                       var class_stu = res.data[0].class_assoc;
                            angular.forEach($scope.Items1, function (item) {
                                 var class_name = item.label.split(' ');
                                 //console.log(class_name);
                                if(class_name[1] == class_stu) {
                                   // console.log(class_stu);
                                  $scope.$parent.student_class = class_stu;  
                                }
                                //if(arr_classes, item.value)
                                //item.value = $scope.selectedAll;
                            });
                      
                   }
                   
                } else
                {
                    alert('Service not available');
                }
                $scope.msg = res.msg;
                // console.log(res.status);
                // $location.path('/dashboard');
            });

        }
       
        $scope.init = function () {
            if($scope.user_type) {
                $scope.student = ($scope.user_type == 1 ? true: false);
                $scope.teacher = ($scope.user_type == 2 ? true: false);
            }
            else
            {
                $scope.student = true;
            }
       }
        if($scope.user_id) {
            $scope.get_user($scope.user_id, $scope.user_type);
        }
       
        $scope.checkAll();

}]);

myApp.controller('ManageContent', ['$scope', 'commonServices', '$location', '$window', '$routeParams','$filter', function ($scope, commonServices, $location, $window, $routeParams,$filter) {
        $scope.checkAll = function () {
            if ($scope.selectedAll) {
                $scope.selectedAll = true;
            } else {
                $scope.selectedAll = false;
            }
            // var i;
            // i = 1;
            angular.forEach($scope.Items, function (items) {
                angular.forEach(items.Name, function (item) {
                    item.value = $scope.selectedAll;
                });
            });


        };
        $scope.content_type = $routeParams['content_type'];
        $scope.content_id = $routeParams['content_id'];
        $scope.active_tab = 'content';
        $scope.Items = [{
                Name: [{label: "Class 1", value: "1"}, {label: "Class 2", value: "2"}]
            }, {
                Name: [{label: "Class 3", value: "3"}, {label: "Class 4", value: "4"}]
            }, {
                Name: [{label: "Class 5", value: "5"}, {label: "Class 6", value: "6"}]
            }, {
                Name: [{label: "Class 7", value: "7"}, {label: "Class 8", value: "8"}]
            }, {
                Name: [{label: "Class 9", value: "9"}, {label: "Class 10", value: "10"}]
            }];
        $scope.save_data = function (content_type) {
           $scope.loader_show = true;
           $scope.loader_hide = false;
           var toPost = {};
           toPost.device = 'desktop';
           toPost.token = '';
           toPost.title = $scope.content_title;
           toPost.content_type = content_type;
           toPost.content_url = $scope.content_url;
           toPost.video_url = $scope.video_url;
           toPost.description = $scope.value;
           if($scope.content_id)
            toPost.content_id = $scope.content_id;
       
            toPost.status = 1;
           if(content_type == 1 || content_type == 3 || content_type == 4) {
               var class_str = '';
                angular.forEach($scope.Items, function (item) {
                    angular.forEach(item.Name, function (item) {
                        // console.log(item.value);
                        if (item.value == true) {
                            var class_name = item.label.split(' ');
                            class_str += class_name[1] + ',';
                        }
                    });
                });
                if(class_str) {
                    toPost.content_class = class_str;
                }
           }
           commonServices.callAction('save_data', toPost).then(function (res) {
               //console.log(res);
               if (res.status == 'Ok' && res.is_success) {
                     //console.log(res.data[0].name);
                  if(content_type == 2) {

                  }
                  else {

                  }

               } else
               {
                   alert('Service not available');
               }
               $scope.msg = res.msg;
               $scope.loader_show = false;
               //$scope.loader_hide = false;
           });

        }

        $scope.listData = function (content_type, is_class) {
           //console.log(user_type);
           var toPost = {};
           toPost.content_type = content_type;
           toPost.device = 'desktop';
           toPost.token = '';
           toPost.platform = '';
           if(is_class) {
             toPost.is_class = 1;
           }
          // console.log(toPost);
           commonServices.callAction('list_data', toPost).then(function (res) {
               if (res.status == 'Ok') {
                   //  console.log(res);
                   $scope.lists = res.data;
                   $scope.viewby = 5;
                   $scope.totalItems = $scope.lists.length;
                   $scope.currentPage = 1;
                   $scope.itemsPerPage = $scope.viewby;
                   $scope.maxSize = 10; //Number of pager buttons to show
               } else
               {
                   alert('Service not available');
               }
               $scope.msg = res.msg;
               // console.log(res.status);
               // $location.path('/dashboard');
           });

        }

        $scope.search_data = function (content_type, is_class) {
           var toPost = {};
           // console.log('hi'+$scope.search_txt);
           toPost.search_txt = $scope.search_txt;
           toPost.user_type = content_type;
           toPost.device = 'desktop';
           toPost.token = '';
           if(is_class) {
               toPost.is_class = is_class;
           }
           commonServices.callAction('search_data', toPost).then(function (res) {
               if (res.status == 'Ok') {
                   $scope.lists = res.data;
                   $scope.viewby = 5;
                   $scope.totalItems = $scope.lists.length;
                   $scope.currentPage = 1;
                   $scope.itemsPerPage = $scope.viewby;
                   $scope.maxSize = 10; //Number of pager buttons to show
               } else
               {
                   alert('Service not available');
               }
               $scope.msg = res.msg;
               // console.log(res.status);
               // $location.path('/dashboard');
           });
        }
        
        $scope.deleteContent = function (content_id, index) {
            $scope.loader_show = true;
            $scope.loader_hide = false;
            var toPost = {};
            toPost.content_id = content_id;
            toPost.device = 'desktop';
            toPost.token = '';
            commonServices.callAction('delete_content', toPost).then(function (res) {
                if (res.status == 'Ok' && res.is_success) {
                   $scope.lists.splice(index, 1);
                } else
                {
                    alert('Service not available');
                }
                $scope.loader_show = false;
                $scope.loader_hide = true;
                
                $scope.msg = res.msg;
                
            });

        }

        $scope.setPage = function (pageNo) {
            $scope.currentPage = pageNo;
        };

        $scope.pageChanged = function () {
            console.log('Page changed to: ' + $scope.currentPage);
        };

        $scope.setItemsPerPage = function (num) {
            $scope.itemsPerPage = num;
            $scope.currentPage = 1; //reset to first paghe
        }
        
        $scope.get_content = function (content_id, content_type) {
            //console.log(user_type);
            var toPost = {};
            toPost.device = 'desktop';
            toPost.token = '';
            toPost.platform = '';
            toPost.content_id = content_id;
            commonServices.callAction('get_content_info', toPost).then(function (res) {
                // console.log(res);
                if (res.status == 'Ok' && res.is_success) {
                       //console.log(res.data[0]);
                       $scope.content_title = res.data[0].title;
                       $scope.content_id = content_id;
                       $scope.content_type = res.data[0].content_type;
                       $scope.video_url = res.data[0].video_url;
                       $scope.content_url = res.data[0].content_url;
                       $scope.value = res.data[0].description;
                       if(content_type == 1 || content_type == 3 || content_type == 4) {
                           var arr_classes = res.data[0].content_class.split(',');
                      // console.log(arr_classes);
                       angular.forEach($scope.Items, function (items) {
                            angular.forEach(items.Name, function (item) {
                                 var class_name = item.label.split(' ');
                                 //console.log(class_name);
                                if($filter('filter')(arr_classes, class_name[1]).length) {
                                    console.log($filter('filter')(arr_classes, class_name[1]));
                                  item.value = true;  
                                }
                                //if(arr_classes, item.value)
                                //item.value = $scope.selectedAll;
                            });
                       });
                       }
                       //alert(res.data[0].description);
                   
                } else
                {
                    alert('Service not available');
                }
                $scope.msg = res.msg;
                // console.log(res.status);
                // $location.path('/dashboard');
            });

        }
        
        if($scope.content_id) {
            //alert(1);
            $scope.get_content($scope.content_id, $scope.content_type);
        }
       
    $scope.checkAll();     
}]);

myApp.controller('TabController', function ($scope) {
        $scope.tab = 1;

        $scope.setTab = function (tabId) {
           // alert("SEt"+tabId);
            $scope.tab = tabId;
        };

        $scope.isSet = function (tabId) {
            //alert(tabId);
            return $scope.tab === tabId;
        };
});

app.directive('ckEditor', function() {
  return {
    require: '?ngModel',
    link: function(scope, elm, attr, ngModel) {
        var ck = CKEDITOR.replace(elm[0]);

      //if (!ngModel) return;

      ck.on('pasteState', function() {
         // alert('hello');
        scope.$apply(function() {
          ngModel.$setViewValue(ck.getData());
        });
      });

      ngModel.$render = function(value) {
        ck.setData(ngModel.$viewValue);
      };
    }
  };
});