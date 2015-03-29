/*******************************************************************************
 * TEMS is a Time and Expense Management program developed by Initechs, LLC.
 * Copyright (C) 2009 - 2012 Initechs LLC.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY INITECHS, INITECHS DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact Initechs headquarters at 1841 Piedmont Road, Suite 301,
 * Marietta, GA, USA. or at email address contact@initechs.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display od the "Initechs"
 * logo. If the display of the logo is not reasonably feasible for technical
 * reasons, the Appropriate Legal Notices must display the words "Powered by
 * Initechs".
 * 
 ******************************************************************************/

function showTips(userEntry, refTable, filter, refField, fieldID) {

	var hintplace = "#" + fieldID;
	var dataSource = "core/model/GetTips.php?userentry=" + userEntry + "&table=" + refTable + "&field=" + refField;
	if (filter != null)
		dataSource += "&addl_cond=" + filter;
	
	var cache = {};

	$(hintplace).autocomplete({
    	source : function(request, response) {
    		var term = request.term;
    		if (term in cache) {
    			response (cache[ term ] );
    			return;
    		}
    		$.getJSON(dataSource, request, function(data, status, xhr) {
    			cache[ term ] = data;
    			response( data );
    		});
    	},
    	minLength: 0,
    	delay: 100
    });
	return;
}



$(function() {
    $("#menu-list").sortable({
    	connectWith : '#remove-menu',
    	opacity : 0.5,
    	dropOnEmpty : true
    });
    $(".pgm-list").draggable({
    	connectToSortable : "#menu-list",
    	helper : "clone",
    	revert : "invalid"
    });
    $("#remove-menu").droppable({
    	accept : '#menu-list > tr',
    	tolerance : "touch",
    	drop : function(event, ui) {
    		ui.draggable.remove();
    	}
    });
});



function calendar(id, datefmt) {
    $(id).datepicker({
    	showOn : "both",
    	dateFormat : datefmt
    });
}



$(function() {
    var loginId  = $("#login-id"), 
    	password = $("#password"),
    	regEmail = $("#registration-id"),
    	loginErr = $("#login-error"),
    	regErr   = $("#registration-error"),
    	allFields = $([]).add(loginId).add(password).add(loginErr).add(regEmail).add(regErr);
    
    $("#login-form").dialog({
    	modal : true,
    	autoOpen : false,
    	resizable : false,
    	width : 350,
    	buttons : {
    		"Login" : function() {
    			var userLoginAction = userLogin(loginId.val(), password.val());
    			userLoginAction.success(function (data) {
    				if (data != "Successful") {
    					loginErr.text(data).addClass("ui-state-highlight");
    					setTimeout(function() {loginErr.removeClass("ui-state-highlight", 1500); }, 500);
    				} else {
        				$("#login-form").dialog("close");
    					window.location.href = "index.php";
    				}
    			});
    		},
    		Cancel : function() {
    			loginErr.text("");
    			$(this).dialog("close");
    		}
    	},
    	close : function() {
    		loginErr.text("");
    		allFields.val("").removeClass("ui-state-error");
    	}
    });

    $("#user-registration-form").dialog({
    	modal : true,
    	autoOpen : false,
    	resizable : false,
    	width : 350,
    	buttons : {
    		"Register" : function() {
    			var uesrReg = userSelfRegistration(regEmail.val());
    			uesrReg.success(function (data) {
    				if (data != "Successful") {
        				regErr.text(data).addClass("ui-state-highlight");
        				setTimeout(function() {regErr.removeClass("ui-state-highlight", 1500); }, 500);
        			} else {
        				$("#user-registration-form").dialog("close");
        				alert("Thank you for registering to accesses TEMS. An email is sent to your email address with your personal link and further instruction.");
        			}
    			});
    		},
    		Cancel : function() {
    			regErr.text("");
    			$(this).dialog("close");
    		}
    	},
    	close : function() {
    		regErr.text("");
    		allFields.val("").removeClass("ui-state-error");
    	}
    });
    
    $("#login-button").button().click(function() {
    	$("#login-form").dialog("open");
    });
    
    $("#register-button").button().click(function() {
    	$("#user-registration-form").dialog("open");
    });
    
});


function enableCreate(action, heading) {
	$("#Error-Msg").text("");
	$("#Successful-Msg").text("");
	$(".option-heading").text(heading);
	$(".Hide-On-All, .Hide-On-Create").hide();
	$(".Show-On-Create, .Enable-On-Create").show();
	$(".Enable-On-Create").removeAttr("disabled");
	$(".Enable-On-Create").not(":radio").val("");
	$(".receipt-image").remove();
	$(":button").hide();
	$("#Go-Back-Button").show();
	$("#Save-Button").html("<input type='submit' value='Save' " +
			"onclick=\"updateBORequest('" + action + "', 'Enable-On-Create Show-On-Create')\">");
}


function enableCopy(action, heading) {
	$("#Error-Msg").text("");
	$("#Successful-Msg").text("");
	$(".option-heading").text(heading);
	$(".Hide-On-All, .Hide-On-Create").hide();
	$(".Show-On-Create, .Enable-On-Create").show();
	$(".Enable-On-Create").removeAttr("disabled");
	$(".receipt-image").remove();
	$(":button").hide();
	$("#Go-Back-Button").show();
	$("#Save-Button").html("<input type='submit' value='Save' " +
			"onclick=\"updateBORequest('" + action + "', 'Enable-On-Create Show-On-Create')\">");
}


function enableEdit(action, heading) {
	$("#Error-Msg").text("");
	$("#Successful-Msg").text("");
	$(".option-heading").text(heading);
	$(".Hide-On-All, .Hide-On-Edit").hide();
	$(".Show-On-Edit, .Enable-On-Edit").show();
	$(".Enable-On-Edit").removeAttr("disabled");
	$(":button").hide();
	$("#Go-Back-Button").show();
	$("#Save-Button").html("<input type='submit' value='Save' " +
			"onclick=\"updateBORequest('" + action + "', 'Enable-On-Edit Show-On-Edit')\">");
}


function enablePrint(action, heading) {
	$("#Error-Msg").text("");
	$("#Successful-Msg").text("");
	$(".option-heading").text(heading);
	$(".Hide-On-All, .Hide-On-Print").hide();
	$(".Show-On-Print, .Enable-On-Print").show();
	$(".Enable-On-Print").removeAttr("disabled");
	$(":button").hide();
	$("#Go-Back-Button").show();
	var printAction = "<input type='submit' value='Print' onclick=\"updateBORequest('" + action + "', 'Enable-On-Print Show-On-Print')\">";
	if (document.getElementById("Print-Button") == null) {
		$("#Print-Button").html(printAction);
	} else {
		$("#Print-Button").replaceWith(printAction);
	}

}



function enableDelete(action) {
	updateBORequest(action);
}

function enableBrowse() {
	$(".Hide-On-All, .Hide-On-Browse").hide();
	$(".Show-On-Browse").show();
}

function enableAction(action, parm) {
	updateBORequest(action, null, parm);
}

function enableChangePassword(action, heading) {
	$("#Error-Msg").text("");
	$("#Successful-Msg").text("");
	$(".option-heading").text(heading);
	$(".Hide-On-All, .Enable-On-Create, .Hide-On-Edit").hide();
	$(".Show-On-ChangePassword, .Enable-On-ChangePassword").show();
	$(".Enable-On-ChangePassword").removeAttr("disabled");
	$(":button").hide();
	$("#Go-Back-Button").show();
	$("#Save-Button").html("<input type='submit' value='Save' " +
			"onclick=\"updateBORequest('" + action + "', 'Enable-On-ChangePassword Show-On-ChangePassword')\">");
}


function clearError(className) {
	
	$("#Error_Msg").text('');
	
	var classArray = className.split(" ");
	$(classArray).each(function(index, eachClass) {
		eachClass = "." + eachClass;
		$(eachClass).each(function(i, e){
			if ((this.type == 'text')
				|| (this.type == 'password')
				|| ((this.type == 'radio') && (this.checked == true))
				|| ((this.type == 'checkbox') && (this.checked == true))
				|| ((this.type == 'select-one') || (this.type == 'select-multiple'))) {
					$("#" + this.name + "-error").text('');
					$("#" + this.name + "-fld").removeClass('error_field');					
			}
		});
	});
	return;
}


function showError(errorJSON) {
	
	var errorLocn = $("#Error-Msg");
	var error = JSON && JSON.parse(errorJSON) || $.parseJSON(errorJSON);

	for (var field in error) {
		if (field == 'nocategory') {
			errorLocn.html(error[field]);
		} else if (error.hasOwnProperty(field)) {
			  $("#" + field + "-error").html(error[field]);
			  $("#" + field + "-fld").addClass('error_field');
		}
	}
}


function advancedSearchData(elementsHavingClass) {

	var glowFlag = false;
	var advCond = "";
	$(elementsHavingClass).each(function(index, element) {
		if (((this.type == 'text')
			|| ((this.type == 'radio') && (this.checked == true))
			|| ((this.type == 'checkbox') && (this.checked == true))
			|| ((this.type == 'select-one') || (this.type == 'select-multiple')))) {
			var fldEntry = $(this).val();
			if (fldEntry.length > 0)
				glowFlag = true;
			if (advCond.length > 0)
				advCond += '&';
			advCond += this.name + "=" + fldEntry;
		}
	});
	glowAdvSearch(glowFlag);
	return(advCond);
}

	
$(function() {
   	$( "#page-tabs" ).tabs();
});


$(function() {
	
	$(document).tooltip({
		position: {
			my: "left+400 bottom"
		}
	});

	$("#Advanced-Search-Form").dialog({
		autoOpen: false,
		closeOnEscape: true,
		width: 350,
		resizable: true,
		modal: true,
			show: {
				effect: "fade",
				duration: 500
			},
			hide: {
				effect: "fade",
				duration: 1000
			},
		buttons: {
			"Search": function() {
				$( this ).dialog( "close" );
				var advCond = advancedSearchData(".advance-search");
				fillupListWrapper(advCond);
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
		}
	});

	$("#Advance-Search-Button")
		.button()
		.click(function() {
			$("#Advanced-Search-Form").dialog("open");
	});

});

function glowAdvSearch(glowFlag) {
	if (glowFlag == true) {
		$("#Advance-Search-Button").button().switchClass("ui-state-default", "Advance-Search-Button-Label-Activated");		
	} else {
		$("#Advance-Search-Button").button().switchClass("Advance-Search-Button-Label-Activated", "ui-state-default");		
	}
}

function cascadeOption(refDisplayFieldIds, refDbFields, targetDisplayFieldId, query, distinctFields) {
	
// If multiple of field ids are sent via refDisplayFieldIds and refDbFields, they must be seperated by space.
// Also refDisplayFieldIds and refDbFields MUST have one-to-one matching.
	
	$('#'+targetDisplayFieldId).find('option').remove().end();							// Clear the target list
	
	// Get the user selected values from the lists of the flieds (refFieldDisplayIds) and make a string seperated by ||
	// and field list also seperated by ||
	var i = 0;
	var idVal = "";
	var keyList = "";

	var refDisplayFieldIdArray = refDisplayFieldIds.split(" ");
	var refDbFieldArray = refDbFields.split(" ");

	for (i=0; i<refDisplayFieldIdArray.length; i++) {
		if (idVal.length > 0) {
			idVal += "||";
			keyList += "||";
		}
		idVal += $("#"+refDisplayFieldIdArray[i]+"-fld").find("option:selected").val();			// Get the parent value
		keyList += refDbFieldArray[i];
	}
		
	$.ajax({
		url: 'core/model/GetDependentOptions.php',
		type: 'GET',
		data: {
			query : query,
			keys : keyList,
			values : idVal,
			distinct : distinctFields
			},
		datatype: 'json',
		cache: false,
		success: function(data) {
			
			data=JSON.parse(data);
			var ddl = document.getElementById(targetDisplayFieldId);
			var option = document.createElement('option');
			option.value = '';
			option.text = '--Select--';
			ddl.appendChild(option);

			$.each(data, function(index, dataObject) {
				var option = document.createElement('option');
				option.value = dataObject.value;
				option.text = dataObject.text;
				ddl.appendChild(option);					
			});
		},
		error: function(jxhr) {
			alert(jxhr.responseText);
		}
	});

}

function showUploadedFile (file, displayLocation) {
	var list = document.getElementById(displayLocation),
  		li   = document.createElement("li"),
  		object  = document.createElement("object");
	object.data = file;
	li.appendChild(object);
	list.appendChild(li);
}