$(document).ready(function() {
	$('.select-dropdown-listing input[type=checkbox]').each(function() {
			var $this = $(this);
			var id = $(this).attr('id');
		   $(this).wrap('<span class="c-form"></span>');		   
		   $(this).after('<label for="'+ id + '"></label>');     
	});	
	$('.select-dropdown-listing input[type=radio]').each(function() {
			var $this = $(this);
			var id = $(this).attr('id');
		  // $(this).wrap('<span class="c-form radio-form"></span>');		   
		   //$(this).after('<label for="'+ id + '"></label>');     
	});
	$(document).delegate('.select-class i', 'click', function(){
		$('.select-dropdown').slideToggle(200);
	});		
   // $("#datepicker").datepicker();
	
	/*$(".tabbing-list a").click(function() {
		$(".tabbing-list").find("a").removeClass("active");
		$(this).addClass("active");
		var getLink = $(this).attr("href");
		$(".tabbing").removeClass("active-content");
		$(".tabbing").not(getLink).css("display", "none");
        $(getLink).fadeIn();
	});*/
});
