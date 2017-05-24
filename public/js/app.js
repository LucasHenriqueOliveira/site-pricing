var app = angular.module('app', ['ngAnimate', 'ngSanitize', 'ui.bootstrap']);

app.controller('appController', ['$scope', '$window', function ($scope, $window) {

    $(document).ready(function () {
        $('#myCarousel').carousel({
            interval: 3000
        });
        if($(window).width() >= 768){
            $('.carousel .item').each(function () {
                var next = $(this).next();
                if (!next.length) {
                    next = $(this).siblings(':first');
                }
                next.children(':first-child').clone().appendTo($(this));
                if (next.next().length > 0) {
                    next.next().children(':first-child').clone().appendTo($(this));
                } else {
                    $(this).siblings(':first').children(':first-child').clone().appendTo($(this));
                }
            });
        }
    });
}]);