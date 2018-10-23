jQuery(document).ready(function($) {
  $('.banner-fade').bjqs({
    animtype      : 'slide',
    height        : $(this).attr('data-height'),
    width         : $(this).attr('data-width'),
    responsive    : true,
    randomstart   : true,
	showmarkers   : false,
  });

 
});