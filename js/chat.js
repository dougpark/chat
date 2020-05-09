// dnp datastore object
class Ds {
	constructor() {
		// hash for conversation message to prevent large data downloads every request
		this.conversationHash = '';
		this.loggedUserId = '';
		this.loggedUserName = '';
		this.toUserId = '';
		this.toUserName = '';

		this.TOUSERNAME = 'toUserName';
	}

	setLoggedUserId(id) {
		this.loggedUserId = id;
	}
	setLoggedUserName(name) {
		this.loggedUserName = name;
	}
	setToUserId(id) {
		this.toUserId = id;
	}
	setToUserName(name) {
		this.toUserName = name;
	}
}

let ds = new Ds();



$(document).ready(function () {

	// dnp udpate datastore object
	ds.setLoggedUserId(getUserData('loggeduserid'));
	ds.setLoggedUserName(getUserData('loggedusername'));
	//ds.setToUserId(getUserData('touserid'));

	// load first page
	//$('#showContacts')[0].click();
	updateUserChat();
	//console.log('document loaded')

	// check for new users every 6 seconds
	setInterval(function () {
		updateUserList();
		updateUnreadMessageCount();
	}, 60000);

	// check for updates every .5 seconds
	setInterval(function () {
		showTypingStatus();
		updateUserChat();
	}, 5000);

	// broken - scroll to bottom of chat list
	$(".messages").animate({
		scrollTop: $(document).height()
	}, "fast");

	// select active chat buddy
	$(document).on("click", '#profile-img', function (event) {
		$("#status-options").toggleClass("active");
	});

	// hmmm
	$(document).on("click", '.expand-button', function (event) {
		$("#profile").toggleClass("expanded");
		$("#contacts").toggleClass("expanded");
	});

	// hmmm
	$(document).on("click", '#status-options ul li', function (event) {
		$("#profile-img").removeClass();
		$("#status-online").removeClass("active");
		$("#status-away").removeClass("active");
		$("#status-busy").removeClass("active");
		$("#status-offline").removeClass("active");
		$(this).addClass("active");
		if ($("#status-online").hasClass("active")) {
			$("#profile-img").addClass("online");
		} else if ($("#status-away").hasClass("active")) {
			$("#profile-img").addClass("away");
		} else if ($("#status-busy").hasClass("active")) {
			$("#profile-img").addClass("busy");
		} else if ($("#status-offline").hasClass("active")) {
			$("#profile-img").addClass("offline");
		} else {
			$("#profile-img").removeClass();
		};
		$("#status-options").removeClass("active");
	});

	// select a contact
	$(document).on('click', '.contact', function () {
		$('.contact').removeClass('active');
		$(this).addClass('active');
		var to_user_id = $(this).data('touserid');
		showUserChat(to_user_id);
		$(".chatMessage").attr('id', 'chatMessage' + to_user_id);
		$(".chatButton").attr('id', 'chatButton' + to_user_id);

		//dnp save to datastore
		ds.setToUserId(to_user_id);

		// dnp TBD would be a good place to update database 
		// chat_users.current_session = to_user_id for userid (loggedUserId)
		// also add typing status to chat_users
		loadUserDetails(to_user_id, ds.TOUSERNAME);
		saveBuddyId(ds.loggedUserId, to_user_id);

		// dnp save the current buddy to DOM
		setUserData({
			attr: "touserid",
			value: to_user_id
		});

	});


	// dnp click on send button -- this one works --
	// find by unique class
	$(".chatButton").click(function () {
		var to_user_id = $(this).attr('id');
		to_user_id = to_user_id.replace(/chatButton/g, "");
		//e.preventDefault();
		//e.stopPropagation();
		$('.chatMessage').focus();
		sendMessage(to_user_id);
		return false;
	});

	//dnp look for Enter key on button
	$('.chatMessage').keyup(function (e) {

		if (e.which == 13) {
			//e.preventDefault();
			//e.stopPropagation();
			$(this).blur();
			$('.chatButton').focus().click();
			$('.chatMessage').focus();
			return false;
		}
	});

	// original
	// hmmm submit button
	// $(document).on("click", '.submit', function (event) {
	// 	var to_user_id = $(this).attr('id');
	// 	//console.log("to_user_id= " + to_user_id);

	// 	to_user_id = to_user_id.replace(/chatButton/g, "");
	// 	console.log('submit clicked, sending to server')
	// 	sendMessage(to_user_id);
	// });



	//dnp listen for return key press submit button
	// $(document).keydown(function (event) {
	// 	//console.log(event);
	// 	if (event.which == 13) {
	// 		event.preventDefault();
	// 		var to_user_id = event.target.id;
	// 		to_user_id = to_user_id.replace(/chatMessageButton/g, "");
	// 		sendMessage(to_user_id);
	// 	}

	// });


	// send typing status to server for logged-in user to Yes
	$(document).on('focus', '.messageInput', function () {
		//dnp get to_user_id here
		// send to update typing to_user_id
		const tu = getUserData('touserid');

		var is_type = 'yes';

		// dnp test saving typing status 
		saveTypingStatus(is_type, ds.loggedUserId);

		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				is_type: is_type,
				buddy_id: tu,
				action: 'update_typing_status'
			},
			success: function () {

			}
		});
	});

	// send typing status to server for logged-in user to No
	$(document).on('blur', '.messageInput', function () {

		const tu = getUserData('touserid');

		var is_type = 'no';

		// dnp test saving typing status 
		saveTypingStatus(is_type, ds.loggedUserId);

		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				is_type: is_type,
				buddy_id: tu,
				action: 'update_typing_status'
			},
			success: function () {

			}
		});
	});
});






// dnp playing with meta-data datasets
// save in DOM index.php
function setUserData(config) {
	$("#user_data")[0].dataset[config.attr] = config.value;
}

function getUserData(attr) {
	return $("#user_data")[0].dataset[attr];
}

// (function () {
// 	'use strict';
// 	window.addEventListener('load', function () {
// 		// Get the forms we want to add validation styles to
// 		var forms = document.getElementsByClassName('chatMessageForm');
// 		// Loop over them and prevent submission
// 		var validation = Array.prototype.filter.call(forms, function (form) {
// 			form.addEventListener('submit', function (event) {

// 				event.preventDefault();
// 				event.stopPropagation();


// 			}, false);
// 		});
// 	}, false);
// })();

// dnp submit button
// function submitMessage(event) {
// 	var to_user_id = $(this).attr('id');
// 	//console.log("to_user_id= " + to_user_id);

// 	to_user_id = to_user_id.replace(/chatButton/g, "");
// 	console.log('submit clicked, sending to server')
// 	sendMessage(to_user_id);
// }

// dnp get user details based on userid
function loadUserDetails(userId, action) {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		dataType: "json",
		data: {
			userid: userId,
			action: 'get_user_details'
		},
		success: function (response) {
			var user = response[0];


			// store username in ds object
			if (action == ds.TOUSERNAME) {
				ds.setToUserName(user.username);
			}

		}
	});
}

// dnp save the contact the loggedUser is chatting with
function saveBuddyId(loggedUserId, buddyId) {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		dataType: "json",
		data: {
			loggedUserId: loggedUserId,
			buddyId: buddyId,
			action: 'save_buddy_id'
		},
		success: function (response) {


		}
	});
}

// dnp save typing status for loggedUser
function saveTypingStatus(status, loggedUserId) {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		data: {
			is_type: status,
			loggedUserId: loggedUserId,
			action: 'save_typing_status'
		},
		success: function () {

		}
	});
}

// dnp get the status on one typing
function loadTypingStatus(loggedUserId, buddyId) {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		data: {
			loggedUserId: loggedUserId,
			buddyId: buddyId,
			action: 'load_typing_status'
		},
		dataType: "json",
		success: function (response) {
			$('#isTyping').html(response.message);

		}
	});
}

// get user list from server -- seems to only update the status of users
function updateUserList() {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		dataType: "json",
		data: {
			action: 'update_user_list'
		},
		success: function (response) {
			var obj = response.profileHTML;
			Object.keys(obj).forEach(function (key) {
				// update user online/offline status
				if ($("#" + obj[key].userid).length) {
					if (obj[key].online == 1 && !$("#status_" + obj[key].userid).hasClass('online')) {
						$("#status_" + obj[key].userid).addClass('online');
					} else if (obj[key].online == 0) {
						$("#status_" + obj[key].userid).removeClass('online');
					}
				}
			});
		}
	});
}

// send the message to the server to to_user_id
function sendMessage(to_user_id) {
	message = $(".messageInput input").val();
	//console.log('message input= ' + message);
	$('.messageInput input').val('');
	if ($.trim(message) == '') {
		return false;
	}
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		data: {
			to_user_id: to_user_id,
			chat_message: message,
			hash: ds.conversationHash,
			action: 'insert_chat'
		},
		dataType: "json",
		success: function (response) {
			// dnp hash
			// this hash should always be new because we just sent a new message
			ds.conversationHash = response.hash;

			$('#conversationSection').html(response.conversation);

			scrollPageToBottom();

		}
	});
}

//dnp https://stackoverflow.com/questions/270612/scroll-to-bottom-of-div
function scrollDivToBottom(id) {
	var div = document.getElementById(id);
	$('#' + id).animate({
		scrollTop: div.scrollHeight - div.clientHeight
	}, 500);
}

//dnp https://stackoverflow.com/questions/4249353/jquery-scroll-to-bottom-of-the-page
function scrollPageToBottom() {
	var div = document.getElementById('right');
	var div2 = $('#right2')[0];
	var h = $(window).height();
	var doc = $(document).height();
	$("html, body").animate({
		scrollTop: $(document).height() - h
	}, 500);

}

// called when select a contact to show the messages with that contact by to_user_id
function showUserChat(to_user_id) {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		data: {
			to_user_id: to_user_id,
			hash: ds.conversationHash,
			action: 'show_chat'
		},
		dataType: "json",
		success: function (response) {
			// dnp hash
			let hash = response.hash;

			// only update page if hash is new
			if (hash != ds.conversationHash) {
				// user info part
				$('#userSection').html(response.userSection);
				// message chat conversation part
				$('#conversationSection').html(response.conversation);
				// reset unread indicator
				$('#unread_' + to_user_id).html('');

				scrollPageToBottom();
				ds.conversationHash = hash;
			}
		}
	});
}

// get conversation based on to_user-id
function updateUserChat() {
	$('li.contact.active').each(function () {
		var to_user_id = $(this).attr('data-touserid');
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				to_user_id: to_user_id,
				hash: ds.conversationHash,
				action: 'update_user_chat'
			},
			dataType: "json",
			success: function (response) {
				// dnp hash
				let hash = response.hash;
				//console.log('updateUserChat hash = ' + hash);
				// only update page if hash is new
				if (hash != ds.conversationHash) {
					$('#conversationSection').html(response.conversation);
					scrollPageToBottom();
					ds.conversationHash = hash;
				}
			}
		});
	});
}

// update unread message count in user list for a to_user_id
function updateUnreadMessageCount() {
	$('li.contact').each(function () {
		if (!$(this).hasClass('active')) {
			var to_user_id = $(this).attr('data-touserid');
			$.ajax({
				url: "chat_action.php",
				method: "POST",
				data: {
					to_user_id: to_user_id,
					action: 'update_unread_message'
				},
				dataType: "json",
				success: function (response) {
					if (response.count) {
						$('#unread_' + to_user_id).html(response.count);
					}
				}
			});
		}
	});
}

// get typing status from server for to_user_id
function showTypingStatus() {

	//dnp test
	//const user_data = $('#user_data')[0];
	const un = getUserData('loggedusername');
	const ui = getUserData('loggeduserid');
	const tu = getUserData('touserid');
	const tn = getUserData('tousername');
	setUserData({
		attr: 'test',
		value: 'doug'
	});
	const test = getUserData('hey');


	$('li.contact.active').each(function () {
		var to_user_id = $(this).attr('data-touserid');
		const tu = getUserData('loggeduserid');

		// dnp test get status of typing
		loadTypingStatus(ds.loggedUserId, ds.toUserId);

		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				to_user_id: to_user_id,
				buddy_id: tu,
				action: 'show_typing_status'
			},
			dataType: "json",
			success: function (response) {
				$('#isTyping').html(response.message);
				// + to_user_id	
			}
		});
	});
}