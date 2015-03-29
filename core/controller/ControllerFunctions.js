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


function updateBORequest(action, dataClass, parm) {
	
	if (action.substr(0,6) == 'Delete') {
		if (!confirm("Are you sure you want to delete?"))
			return;
	} else if (action.substr(0,4) == 'Undo') {
		if (!confirm("Are you sure you want to undo it?"))
			return;
	}
	
	if (dataClass != null) {
		clearError(dataClass);
	}
	
	var globalParmAry = new Array();
	
	var pgm = getBaseDir() + "/core/controller/UpdateBORequest.php";
	var formData = 'action=' + action;

	if (parm != null) {
		parmArray = parseParm(parm);
		$.each(parmArray, function(index, element) {
			formData += "&" + element + "=" + parmArray[element];
			globalParmAry.push(element);
		});
	}		

	var parmArray = getUrlVars();
	$.each(parmArray, function(index, element) {
		if (element != 'action') {
			if ($.inArray(element, globalParmAry) == -1) {
				formData += "&" + element + "=" + parmArray[element];
			}
		}
	});
	
	if (dataClass != null) {
		var dataClassArray = dataClass.split(' ');
		for (var i=0; i<dataClassArray.length; i++) {
			formData += makeAJAXData("." + dataClassArray[i]);
		}
	}
	
	$.ajax({
	    type : "POST",
	    url : pgm,
	    data : formData,
	    error : function(jqXHR, ajaxSettings, thrownError) {
	    		showAjaxError(jqXHR, ajaxSettings, thrownError);
	    },
	    success : function(response, textStatus, jqXHR) {
	    	if ((response == null) || (response.substr(0, 5) == "Failed"))
				alert("Operation was not successful, but no error was detected. Operation was ignored! It is commonly a programming issue.");
			else if (response.substr(0, 10) != "Successful")
				showError(response);
			else if ((action.substr(0, 6) == 'Delete')				// All operations are successful. 
					|| (action == 'SubmitWeeklyTime')
					|| (action == 'ApproveWeeklyTime')
					|| (action == 'UndoInvoice'))
						window.history.go(-1);
			else if (action.substr(0, 5) == 'Print') {
						var url = "core/controller/PrintReport.php?" + formData;
						window.location.href = url;
			} else if (action == 'CompleteRegistration')
				window.location.href = "index.php";
			else {
					$("#Successful-Msg").html("Last operation was successful");
					if (action.substr(0, 6) == 'Create') {
						var url = window.location.href;
						url = url.replace("action=Create", "action=Browse");
						if ((uidPosition = url.search("&uid=")) != -1)
							url = url.substr(0, uidPosition);
						url += "&" + response.substr(13);
						window.location.replace(url);
					} else {
						window.history.go(0);												
					}
			} 
	    }
	});
    return;
}


function makeAJAXData(elementsHavingClass) {

	var formData = "";
	$(elementsHavingClass).each(function(index, element) {
		if ((this.type == 'text')
			|| (this.type == 'password')
			|| (this.type == 'file')
			|| ((this.type == 'radio') && (this.checked == true))
			|| ((this.type == 'checkbox') && (this.checked == true))
			|| ((this.type == 'select-one') || (this.type == 'select-multiple'))) {
				
			formData += '&' + this.name + '=' + encodeURIComponent($(this).val());
		}
	});
	return(formData);
}


function getUrlVars () {
	
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for (var i=0; i<hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}


function parseParm (parm) {
	
	var vars = [], hash;
	var hashes = parm.split('&');
	for (var i=0; i<hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}


function getBaseDir () {
	
	var urlpart = window.location.href.slice(0, window.location.href.indexOf('?'));
	var baseDir = urlpart.slice(0, (urlpart.lastIndexOf('/')));
	return baseDir;
}


function actionURL(url) {
	url = 'index.php?' + url;
	window.location.href = url;
}


function userLogin(loginId, password) {

	return $.ajax({
				type : "POST",
				url : "login/controller/Login.php",
				data : {
					loginId : loginId,
					password : password
				},
				error : function(jqXHR, textStatus, thrownError) {
							showAjaxError(jqXHR, ajaxSettings, thrownError);
				}
	});
}


function userSelfRegistration(regEmail) {
    
	return $.ajax({
    			type : "POST",
    			url : "selfregistration/controller/SelfRegistration.php",
    			data : {
    				regEmail : regEmail
    			},
    			error : function(jqXHR, textStatus, thrownError) {
    						showAjaxError(jqXHR, ajaxSettings, thrownError);
    			}
	});
}




function showAjaxError(jqXHR, ajaxSettings, thrownError) {
	
	alert("AJAX Error" 
			+ "\nStatus = " + jqXHR.status + " (" + jqXHR.statusText + ")"
			+ "\nAJAX Settings = " + ajaxSettings 
			+ "\nError = " + thrownError
			+ "\n--- Details ---\n" + jqXHR.responseText);
}


function uploadImages(fieldId, imageLocation) {

	if (!window.FormData || !window.FileReader) {
		alert("Your browser does not support HTML5. Upgrade your borwser to use this feature.");
		return;
	}
	
	var formdata = new FormData();
	var fileNames = document.getElementById(fieldId);
	
	for ( var i=0; i < fileNames.files.length; i++ ) {
		var file = fileNames.files[i];
	
		if (file.type.match(/image.*/)) {
			var reader = new FileReader();
			reader.onloadend = function (e) { 
				showUploadedFile(e.target.result, imageLocation);
			};
			reader.readAsDataURL(file);
			formdata.append("filesToBeUploaded[]", file);
		}	
	}
		
	$.ajax({
		url: "core/model/UploadImages.php",
		type: "POST",
		data: formdata,
		processData: false,
		contentType: false,
		success: function (res) {
			if (res.substr(0,10) == 'Successful') {
				return (res.substr(13));
			}
		}
	});
}

function printWithDocuments() {
	
	window.print();
	
	$(".hide-document").each(function(index, element) {
		var link = $(element).attr('href');
		var w = window.open(link);
		w.print();
		w.close();		
	});
	
	
}
