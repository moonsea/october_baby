function initSlide() 
{	
	Swipe(document.getElementById('slider'), {  
		auto: 3000,  
		continuous: true,  
		callback: function(pos){
			$("#pointer a").each(function(index, element) {
				$(this).removeClass("active");
			});
			$("#pointer a:nth-child("+(pos+1)+")").addClass("active")  
		}  
	  });	
}