$(document).ready(function(){
    $('.alignment li').on('click',function(){
    	$('.alignment li').removeClass('active');
    	$(this).addClass('active');
    	$('img').attr('src','index.php?c=image&align='+$(this).data('mode'));
    });
});