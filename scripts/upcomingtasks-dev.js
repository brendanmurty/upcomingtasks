// Normalized hide address bar for iOS & Android - https://gist.github.com/1183357
(function( win ){
	var doc = win.document;
	if( !location.hash && win.addEventListener ){
		window.scrollTo( 0, 1 );
		var scrollTop = 1,
			getScrollTop = function(){
				return win.pageYOffset || doc.compatMode === "CSS1Compat" && doc.documentElement.scrollTop || doc.body.scrollTop || 0;
			},
			bodycheck = setInterval(function(){
				if( doc.body ){
					clearInterval( bodycheck );
					scrollTop = getScrollTop();
					win.scrollTo( 0, scrollTop === 1 ? 0 : 1 );
				}	
			}, 15 );
		win.addEventListener( "load", function(){
			setTimeout(function(){
				if( getScrollTop() < 20 ){
					win.scrollTo( 0, scrollTop === 1 ? 0 : 1 );
				}
			}, 0);
		} );
	}
})(this);

// Sort HTML elements - http://james.padolsey.com/javascript/sorting-elements-with-jquery/
$.fn.sortElements = (function(){
    var sort = [].sort;
    return function(comparator, getSortable) {
        getSortable = getSortable || function(){return this;};
        var placements = this.map(function(){
            var sortElement = getSortable.call(this),
                parentNode = sortElement.parentNode,
                nextSibling = parentNode.insertBefore(
                    document.createTextNode(''),
                    sortElement.nextSibling
                );
            return function() {
                if (parentNode === this) {
                    throw new Error(
                        "You can't sort elements if any one is a descendant of another."
                    );
                }
                parentNode.insertBefore(this, nextSibling);
                parentNode.removeChild(nextSibling);
            };
        });
        return sort.call(this, comparator).each(function(i){
            placements[i].call(getSortable.call(this));
        });
    };
})();

// Keep mobile Safari in standalone mode - https://gist.github.com/1042026
if(("standalone" in window.navigator) && window.navigator.standalone){
	var noddy, remotes = true;
	document.addEventListener('click', function(event) {
		noddy = event.target;
		while(noddy.nodeName !== "A" && noddy.nodeName !== "HTML") {
	        noddy = noddy.parentNode;
	    }
		if('href' in noddy && noddy.href.indexOf('http') !== -1 && (noddy.href.indexOf(document.location.host) !== -1 || remotes))
		{
			event.preventDefault();
			document.location.href = noddy.href;
		}
	},false);
}

// Confirm a date is valid, requires (D)D/(M)M/(YY)YY format - http://www.qodo.co.uk/blog/javascript-checking-if-a-date-is-valid/
function valid_date(s){
    var dateFormat = /^\d{1,4}[\.|\/|-]\d{1,2}[\.|\/|-]\d{1,4}$/;
    if(dateFormat.test(s)){
        // Remove leading zeros
        s = s.replace(/0*(\d*)/gi,"$1");
        var dateArray = s.split(/[\.|\/|-]/);
      
        // Month
        dateArray[1] = dateArray[1]-1;

        // Year
        if(dateArray[2].length<4){
            dateArray[2]=(parseInt(dateArray[2])<50)?2000+parseInt(dateArray[2]):1900+parseInt(dateArray[2]);
        }

        var testDate = new Date(dateArray[2],dateArray[1],dateArray[0]);
        if(testDate.getDate()!=dateArray[0]||testDate.getMonth()!=dateArray[1]||testDate.getFullYear()!=dateArray[2]){
            return false;
        }else{
            return true;
        }
    }else{
        return false;
    }
}

$(document).ready(function() {
	
	// Sort tasks by due date
	$(".page_home ul.task-multiple:not(.task-completed) li").sortElements(function(a, b){
	    return $(a).attr('data-due') > $(b).attr('data-due') ? 1 : -1;
	});
	
	// Remove task due date
	$("#button_remove_date,a#button_task_dateremove").click(function(){
		var confirm = window.confirm("Remove this task's due date?");
		if(confirm){
			window.location=window.location.href+'&mode=dueremove';
		}
		return false;
	});
	
	// Delete task
	$("#action-delete a,a#button_task_delete").click(function(){
		var confirm = window.confirm("Are you sure you want to delete this task?");
		if(confirm){
			window.location=window.location.href+'&mode=deletetask';
		}
		return false;
	});
	
	// Setup for unsaved content
	var content_changed = false;
	$("#form_task select,#form_task .select input,#form_task .text,#form_task_edit select,#form_task_edit .select input,#form_task_edit .text,#form_comment textarea").change(function(){
		content_changed = true;
	});

	// Cancel task edit
	$("#button_task_canceledit").click(function(){
		$("a#button_task_edit").click();
		content_changed = false;
		return false;
	});
	
	// Logout
	$("#button_logout").click(function(){
		var confirm = window.confirm("Would you like to logout now?");
		if(confirm){
			window.location='/pages/logout.php';
		}
		return false;
	});

	// Toggle the edit task options
	$("#button_edit,a#button_task_edit").click(function(){
		if($(this).hasClass("active")){// Go to view mode
			$(this).removeClass("active");
			$(".task-single,.date.select,ul.comments,#form_comment,#action-complete,nav.task_actions").removeClass("hidden");
			$("#action-delete").addClass("hidden");
			$("#form_task_edit #task_name").val("");
			$("#form_task_edit .task-location").html("");
			$("#form_task_edit").addClass("hidden");
		}else{// Go to edit mode
			$(this).addClass("active");
			$(".task-single,ul.comments,#form_comment,#action-complete,nav.task_actions").addClass("hidden");
			$("#action-delete").removeClass("hidden");
			var task_name=$(".task-single li .task-name").text();
			var task_location=$(".task-single li .task-location").text();
			$("#form_task_edit #task_name").val(task_name);
			$("#form_task_edit .task-location").html(task_location);
			$("#form_task_edit").removeClass("hidden");
		}
		return false;
	});
	
	// Update task details
	$("#button_update_task").click(function(){
		var date_string=$("#date_day").find(":selected").attr("value")+'/'+$("#date_month").find(":selected").attr("value")+'/'+$("#date_year").find(":selected").attr("value");
		if(valid_date(date_string)){
			content_changed = false;
			$("#form_task_edit").submit();
		}else{
			alert("Please select a valid date!");
		}
		return false;
	});
	
	// New task - toggle due date
	$("input#due_mode_select").click(function(){
		if($(this).is(":checked")){
			$(this).parent("p").removeClass("disabled");
			$("input#due_mode").attr("value","date");
		}else{
			$(this).parent("p").addClass("disabled");
			$("input#due_mode").attr("value","none");
		}
	});

	// Menu - toggle dropdown
	$("a#toggle_nav").click(function(){
		var p=$(this).parent().parent();
		if(p.hasClass('closed')){// Open the menu
			p.removeClass('closed');
			$(this).find('i').attr('class','icon icon-chevron-up');
		}else{// Close the menu
			p.addClass('closed');
			$(this).find('i').attr('class','icon icon-chevron-down');
		}
		return false;
	});

	// Alter height of "down"
	$("#down").css("height","20px");

	// Don't show unsaved content when clicking a submit button!
	$(".buttons .submit").click(function(){
		content_changed = false;
	});

	// Check for unsaved content and warn the user if needed
	window.onbeforeunload=function(ev){
		if(content_changed){
			return "Your changes haven't been saved yet!";
		}
	};
});