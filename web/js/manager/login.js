$(function()
{

    //===== Media item hover overlay =====//
	$('.view').hover(function(){
	    $(this).children(".view-back").fadeIn(200);
	},function(){
	    $(this).children(".view-back").fadeOut(200);
	});

});
