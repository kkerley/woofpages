jQuery(document).ready(function($){
   var $scm_menu = $('.scm-sidebar-child-menu');

   if($scm_menu){
       var $icon_open = $scm_menu.data('icon-menu-open');
       var $disable_js = $scm_menu.data('disable-js');

       if($disable_js !== "on"){
           if($icon_open === ''){
               $icon_open = 'fa-chevron-up';
           }
           var $icon_closed = $scm_menu.data('icon-menu-closed');
           if($icon_closed === ''){
               $icon_closed = 'fa-chevron-down';
           }
           var combined_classes = $icon_open + ' ' + $icon_closed;

           $scm_menu.find('li.parent-item.top-level, li.parent-item.active-trail, li.parent-item.current-page').append('<i class="fa ' + $icon_open + '"></i>');
           $scm_menu.find('li.parent-item:not(.top-level):not(.active-trail):not(.current-page)').append('<i class="fa ' + $icon_closed + '"></i>');

           $('body').on('click', '.scm-sidebar-child-menu .parent-item > i.fa', function(){
               var $this = $(this);
               $this.toggleClass(combined_classes);
               $this.parent('.parent-item').find('> .child-menu').slideToggle('fast');
           });
       }
   }
});