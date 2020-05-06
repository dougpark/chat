$(document).ready(function () {
	// load first page
	//$('#showContacts')[0].click();
	updateUserChat();
	console.log('document loaded')

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
		//console.log('i just selected a new contact');
	});


	// dnp click on send button -- this one works --
	// find by unique class
	$(".chatMessageButton").click(function () {
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
			$('.chatMessageButton').focus().click();
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


	// update typing status to yes
	$(document).on('focus', '.message-input', function () {
		var is_type = 'yes';
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				is_type: is_type,
				action: 'update_typing_status'
			},
			success: function () {

			}
		});
	});

	// update typing status to no
	$(document).on('blur', '.message-input', function () {
		var is_type = 'no';
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				is_type: is_type,
				action: 'update_typing_status'
			},
			success: function () {

			}
		});
	});
});

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

// get user list from server
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

// send the message to the chat buddy
function sendMessage(to_user_id) {
	message = $(".message-input input").val();
	//console.log('message input= ' + message);
	$('.message-input input').val('');
	if ($.trim(message) == '') {
		return false;
	}
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		data: {
			to_user_id: to_user_id,
			chat_message: message,
			action: 'insert_chat'
		},
		dataType: "json",
		success: function (response) {
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
	$("html, body").animate({
		scrollTop: $(document).height()
	}, 500);

}

// called when select a contact to show the messages with that contact
function showUserChat(to_user_id) {
	$.ajax({
		url: "chat_action.php",
		method: "POST",
		data: {
			to_user_id: to_user_id,
			action: 'show_chat'
		},
		dataType: "json",
		success: function (response) {
			// user info part
			$('#userSection').html(response.userSection);
			// message chat conversation part
			$('#conversationSection').html(response.conversation);
			// reset unread indicator
			$('#unread_' + to_user_id).html('');
			//console.log(response.userSection);
			//console.log(response.conversation);
		}
	});
}

function updateUserChat() {
	$('li.contact.active').each(function () {
		var to_user_id = $(this).attr('data-touserid');
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				to_user_id: to_user_id,
				action: 'update_user_chat'
			},
			dataType: "json",
			success: function (response) {
				$('#conversationSection').html(response.conversation);
				scrollPageToBottom();
			}
		});
	});
}

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

function showTypingStatus() {
	$('li.contact.active').each(function () {
		var to_user_id = $(this).attr('data-touserid');
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				to_user_id: to_user_id,
				action: 'show_typing_status'
			},
			dataType: "json",
			success: function (response) {
				$('#isTyping_' + to_user_id).html(response.message);
			}
		});
	});
}