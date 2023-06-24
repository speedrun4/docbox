/*
Template Name: Monster Admin
Author: Themedesigner
Email: niravjoshi87@gmail.com
File: js
*/
$(function() {
    "use strict";
      $(".tst1").click(function(){
           $.toast({
            heading: 'Certeza que é aqui',
            text: 'Foi pelo primeiro',
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'info',
            hideAfter: 3000,  
            stack: 6
          });

     });

      $(".tst2").click(function(){
           $.toast({
        	   text: 'Foi pelo segundo',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'warning',
            hideAfter: 3500, 
            stack: 6
          });

     });
      $(".tst3").click(function(){
           $.toast({
        	   text: 'Foi pelo terceiro',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'success',
            hideAfter: 3500, 
            stack: 6
          });

     });

      $(".tst4").click(function(){
           $.toast({
        	   text: 'Foi pela sala',
            text: 'Use the predefined ones, or specify a custom position object.',
            position: 'top-right',
            loaderBg:'#ff6849',
            icon: 'error',
            hideAfter: 3500
            
          });

     });
});
          
