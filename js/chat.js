$(document).ready(function () {
	setInterval(function () {
		updateUserList();
		updateUnreadMessageCount();
	}, 60000);
	setInterval(function () {
		showTypingStatus();
		updateUserChat();
	}, 5000);
	$(".messages").animate({
		scrollTop: $(document).height()
	}, "fast");
	$(document).on("click", '#profile-img', function (event) {
		$("#status-options").toggleClass("active");
	});
	$(document).on("click", '.expand-button', function (event) {
		$("#profile").toggleClass("expanded");
		$("#contacts").toggleClass("expanded");
	});
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
		console.log('i just selected a new contact');
	});

	//xxx listen for return key press
	$(document).keydown(function (event) {
		//console.log(event);
		if (event.which == 13) {
			event.preventDefault();
			var to_user_id = event.target.id;
			//console.log('to= ' + to_user_id);
			to_user_id = to_user_id.replace(/chatMessage/g, "");
			//console.log("I just hit return " + to_user_id);
			sendMessage(to_user_id);
		}

	});

	$(document).on("click", '.submit', function (event) {
		var to_user_id = $(this).attr('id');
		//console.log("to_user_id= " + to_user_id);

		to_user_id = to_user_id.replace(/chatButton/g, "");

		sendMessage(to_user_id);
	});

	$(document).on('focus', '.message-input', function () {
		var is_type = 'yes';
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				is_type: is_type,
				action: 'update_typing_status'
			},
			success: function () {}
		});
	});
	$(document).on('blur', '.message-input', function () {
		var is_type = 'no';
		$.ajax({
			url: "chat_action.php",
			method: "POST",
			data: {
				is_type: is_type,
				action: 'update_typing_status'
			},
			success: function () {}
		});
	});
});

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
			$('#conversation').html(response.conversation);
			scrollSmoothToBottom('conversation');
		}
	});
}

//dnp https://stackoverflow.com/questions/270612/scroll-to-bottom-of-div
function scrollSmoothToBottom(id) {
	var div = document.getElementById(id);
	$('#' + id).animate({
		scrollTop: div.scrollHeight - div.clientHeight
	}, 500);
}

// called when select a contact to show the message with that contact
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
			$('#userSection').html(response.userSection);
			$('#conversation').html(response.conversation);
			$('#unread_' + to_user_id).html('');
			console.log(response.userSection);
			console.log(response.conversation);
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
				$('#conversation').html(response.conversation);
				scrollSmoothToBottom('conversation');
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