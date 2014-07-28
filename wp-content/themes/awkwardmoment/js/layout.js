/**
 * layout.js
 */
( function( $ ) {
  if($(".entry-content").length){

    $(".entry-title")
      .clone()
      .removeClass("entry-title")
      .addClass("site-content-title")
      .insertAfter($(".site-description"));

    var $offset = $(".entry-content").offset();
    var $headerHeight = $(".masthead-wrapper").height();

    $(document).on('scroll', function(){
      var $scrollTop = jQuery(document).scrollTop();
      if($offset.top < $scrollTop + $headerHeight && $("body").hasClass("middle") == false){
        $("body").addClass("middle");
      }else if($offset.top > $scrollTop + $headerHeight && $("body").hasClass("middle") == true){
        $("body").removeClass("middle");
      }
    });

  }

  // masonry layout
  if($(".summaries-wrapper").length){
    var msnry = new Masonry( '.summaries-wrapper', {
      transitionDuration: 0,
      itemSelector: '.summary'
    });
  }

} )( jQuery );