//select/unselect all elements checkbox
jQuery(document).ready(function() {

// Below code is used to remove all check property if,
// User select/unselect options with name=option2 options.
jQuery(".map2app-post").click(function() {
	jQuery("#selectall").prop("checked", false);
});

/////////////////////////////////////////////////////////////
// JS for Check/Uncheck all CheckBoxes//
/////////////////////////////////////////////////////////////
jQuery("#selectall").click(function() {
	
	jQuery(".map2app-post").each(function () {
		
		if(jQuery(this).parent().parent().css('display') != 'none')
			jQuery(this).prop("checked", jQuery("#selectall").prop("checked"));
	})
	setCountSelected();
})


/////////////////////////////////////////////////////////////
//JS for filter articles by category //
/////////////////////////////////////////////////////////////


    jQuery("#status_select").change(function() {
    	var statValue = jQuery(this).val();
    	var catValue = jQuery('#category_select').val();
    	FilterArticles(statValue,catValue);
    })
    
     jQuery("#category_select").change(function() {
    	var catValue = jQuery(this).val();
    	var statValue = jQuery('#status_select').val();
    	FilterArticles(statValue,catValue);
    })
    
    function FilterArticles(status,cat) {
		jQuery(".map2app-row").hide();
		if(status =='all'&&cat=='all'){
			jQuery(".map2app-row").show();
		}
		else if(status =='all'&&cat!='all'){
			jQuery(".map2app-row."+cat).show();
		}
		else if(status !='all'&&cat=='all'){
			if(status=='selected')
				jQuery(".map2app-row").filter(function(){return jQuery(this).find('.map2app-post:checked').size()}).show();
			else
				jQuery(".map2app-row."+status).show();
		}
		else{	
			if(status=='selected')
				jQuery(".map2app-row."+cat).filter(function(){return jQuery(this).find('.map2app-post:checked').size()}).show();
			else
				jQuery("."+status+"."+cat).show();
		}
		setCountSelected();
	}


//run request manager
ajaxManager.run();

/////////////////////////////////////////////////////////////
//Post submit //
/////////////////////////////////////////////////////////////
jQuery( "#map2app" ).submit(function( event ) {
		
		var url = map2app.ajax_url;
		var loadIcon = map2app.load_icon;
		
		
		
		
		
		var submit = jQuery("#map2app").find(".submit");
		var abort = jQuery("#map2app").find(".abort");
		//change submit button to stop button
		submit.hide();
		abort.show();
		//switch post list to selected
		FilterArticles('selected','all');
		jQuery("#category_select").val('all');
		jQuery("#status_select").val('selected');
		//add a grey div to lock actions on post list
		jQuery("#table-fog").show();
		//for each selected post
		jQuery('.map2app-post:checked').each(function(){
			
			console.log(url);
			var id=jQuery(this).attr('value');
			var lang=jQuery("#lang_selector").val();
			var result;
			if(jQuery(this).children("span:first").hasClass( "uploaded" ))
				jQuery(".status-"+id).html('<span style="color:blue;" class="waiting-uploaded">WAITING</span>');
			if(jQuery(this).children("span:first").hasClass( "error" ))
				jQuery(".status-"+id).html('<span style="color:blue;" class="waiting-error">WAITING</span>');
			else
				jQuery(".status-"+id).html('<span style="color:blue;" class="waiting">WAITING</span>');
			ajaxManager.addReq({
				url:url,
				type:'POST',
				data:{action:'upload_post',id:id,lang:lang},
				 success:function(data){
					result=data.result;
					if(result){
						jQuery(".status-"+id).html('<span style="color:green;" class="uploaded">UPLOADED</span>');
						
						jQuery(".check-"+id).prop("checked","");
						
					}
					else jQuery(".status-"+id).html('<span style="color:red;" class="error">ERROR</span>');
					
					setCountSelected();
				},
				dataType:'json'
			});
			
		});
	
	
	 
		
	  event.preventDefault();
	});


	//update selected post counter
	function setCountSelected(){
		var count=jQuery(".map2app-post:checked").size();
		jQuery(".selected-count").text(count);
	}
	
	//when a single post is selected/unselected
	jQuery(".map2app-post").click(function(){
			setCountSelected();});
	
	//first time
	setCountSelected();
	
//////////////////////////////////
	//STOP SUBMITTING
	/////////////////////////////////
	jQuery(".abort").click(function() {
		jQuery(".waiting-uploaded").parent().html('<span style="color:green;" class="uploaded">UPLOADED</span>');
		jQuery(".waiting-error").parent().html('<span style="color:red;" class="uploaded">ERROR</span>');
		jQuery(".waiting").parent().html('<span style="color:black;">NOT UPLOADED</span>');
		ajaxManager.stop();
	});
});//end of document ready



//ajax request manager
var ajaxManager = (function() {
	
	var url = map2app.ajax_url;
    var requests = [];
    var done=0;
    var loadIcon = map2app.load_icon;
    var ids=[];
    return {
       addReq:  function(opt) {
           requests.push(opt);
       },
       removeReq:  function(opt) {
           if( jQuery.inArray(opt, requests) > -1 )
               requests.splice(jQuery.inArray(opt, requests), 1);
       },
       run: function() {
           var self = this,
               oriSuc;

           if( requests.length ) {
               oriSuc = requests[0].complete;

               requests[0].complete = function() {
                    if( typeof(oriSuc) === 'function' ) oriSuc();
                    requests.shift();
                    self.run.apply(self, []);
               };
               //get post id to be uploaded
               var id=requests[0].data.id;
               jQuery(".status-"+id).html('<img src="'+loadIcon+'" height="20px"> UPLOADING');
               jQuery.ajax(requests[0]).fail(function() {
            	   jQuery(".status-"+id).html('<span style="color:red;">ERROR</span>');
               }).done(function(data){ if(data.result){done++;ids.push(data.id);}});
           } else {  
        	   
        	   var submit = jQuery("#map2app").find(".submit");
               var abort = jQuery("#map2app").find(".abort");
               abort.hide();
       		submit.show(); 
             self.tid = setTimeout(function() {
                self.run.apply(self, []);
             }, 1000);
             //restore upload button
             
      		//hide covering div
      		//add a grey div to lock actions on post list
      			jQuery("#table-fog").hide();
      			
             if(done>0){
            	 //send recap email
         		done=0;
         		
         		
         			jQuery.post(
         					url,
         					{action:'send_recap_email',ids:ids},
         					function(returnedData){
         						
         					},
         					'json'
         				).done(function(data){
         					
         				}
         				).fail(function(data){
         					jQuery(".status-"+requests[0].data.id).html('<span style="color:red;">ERROR</span>');
         				});
         		ids=[];	
             }
           }
       },
       stop:  function() {
           requests = [];
           clearTimeout(this.tid);
       }
    };
}());


