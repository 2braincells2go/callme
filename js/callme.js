/*
 * CallMe v0.0.1
 * Videochat with peerjs
 * Copyright (c) Tóth András
 * Released under the MIT license
 * http://atandrastoth.co.uk/
 * Date: 2015-01-18
 **/
(function($, undefined) {

	this.debug = false;

	this.peer = new Peer({
		key: peerKey,
		debug: 0
	});
	var menuToggle = $("#menu-toggle"),
		usersContainer = $('.sidebar-nav'),
		callModal = $('#call-modal'),
		previewUser = $('#preview_user'),
		startCall = $('.start-call'),
		endCall = $('.end-call'),
		alertBox = $('#alert-box'),
		wrapper = $('#wrapper'),
		theirVideo = $('#their-video'),
		ownVideo = $('#own-video'),
		goFullScreen = $('#full-screen'),
		toggleVideo = $('#toggle-video'),
		toggleAudio = $('#toggle-audio');

	var selectedUser = null;

	$(document).bind('fscreenchange', function(e, state, elem) {
		if ($.fullscreen.isFullScreen()) {
			menuToggle.addClass('hidden');
			theirVideo.removeAttr('height');
			var height = theirVideo.height();
			var targetHeight = screen.height;
			if (height != targetHeight) {
				theirVideo.css({
					top: -(height - targetHeight) / 2
				});
			}
		} else {
			menuToggle.removeClass('hidden');
			theirVideo.attr('height', '100%');
			theirVideo.css({
				top: 0
			});
		}
	});
	toggleVideo.click(function(event) {
		$(this).toggleClass('btn-danger');
		CallMe.camera.toggleVideo();
	});
	toggleAudio.click(function(event) {
		$(this).toggleClass('btn-danger');
		CallMe.camera.toggleAudio();
	});
	goFullScreen.click(function(event) {
		toggleFullScreen()
	});
	menuToggle.click(function(e) {
		wrapper.toggleClass("toggled");
	});
	usersContainer.on('click', 'li', function(event) {
		selectedUser = $(this).data().user;
		$(this).parent('ul').children('li').removeClass("active");
		$(this).addClass("active");
		previewUser.children().remove();
		previewUser.append(selectedUser.preview);
		if (selectedUser.element.find('.btn-default').length == 0 && (selectedUser.call == null || !selectedUser.call.open)) {
			startCall.removeClass('hidden')
		} else {
			startCall.addClass('hidden')
		}
	});
	startCall.eq(0).click(function(event) {
		var call = CallMe.peer.call(selectedUser.peer_id, window.localStream);
		selectedUser.call = call;
		prepareCall(call);
		callModalHandler('show', 'Calling...', selectedUser.name, selectedUser.img, false, true, 'outTonePlay');
	});
	startCall.eq(1).click(function(event) {
		startCall.addClass('hidden');
		acceptCall($(this).data().call);
	});
	endCall.click(function(event) {
		var user = CallMe.users.getUser('name', callModal.find('.modal-body h3').text());
		var bt = false;
		if (user != null) {
			user.conn.send('@system-message:stopCall');
			if (selectedUser != null && user.id == selectedUser.id) bt = !bt;
		}
		callModalHandler('hide', '', '', '', bt, false, 'outToneStop');
		peerCallClose(user.call);
	});
	this.camera = null;
	var camera = function(disp, vSrc, aSrc, canWidth, canHeight) {
		var videoSource = [];
		var audioSource = [];
		vSrc = typeof vSrc == 'undefined' ? 0 : vSrc;
		aSrc = typeof aSrc == 'undefined' ? 0 : aSrc;
		canWidth = typeof canWidth == 'undefined' ? 240 : canWidth;
		canHeight = typeof canHeight == 'undefined' ? 176 : canHeight;
		this.error = null;
		if (typeof MediaStreamTrack === 'undefined') {
			this.error = 'This browser does not support MediaStreamTrack.\n\nTry Google Chrome.';
		} else {
			MediaStreamTrack.getSources(getSources);
		};
		this.init = function() {
			navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
			if ( !! navigator.getUserMedia) {
				var localMediaStream = null;
				var video = document.createElement('video');
				video.muted = true;
				video.width = canWidth;
				video.height = canHeight;
				video.autoplay = true;
				navigator.getUserMedia({
					audio: {
						optional: [{
							sourceId: audioSource[aSrc]
						}]
					},
					video: {
						optional: [{
							sourceId: videoSource[vSrc]
						}]
					}
				}, function(stream) {
					video.src = window.URL.createObjectURL(stream);
					window.localStream = stream;
				}, errorCallback);
				$(video).on('canplay', function() {
					var display = document.querySelector(disp);
					var w = display.width = video.width;
					var h = display.height = video.height;
					var ctx = display.getContext('2d');
					ctx.translate(w, 0);
					ctx.scale(-1, 1);
					setInterval(draw, 40, this, ctx, w, h);
					ajaxCall('com.php', 'getUsers', {}, true);
					CallMe.refreshInterval = setInterval(function() {
						ajaxCall('com.php', 'getUsers', {}, true);
					}, 30000);
				});
			} else {
				this.error = 'getUserMedia is not supported!';
			};
		};
		this.toggleVideo = function() {
			var tr = window.localStream.getVideoTracks()[0];
			tr.enabled = tr.enabled ? false : true;
		};
		this.toggleAudio = function() {
			var tr = window.localStream.getAudioTracks()[0];
			tr.enabled = tr.enabled ? false : true;
		};

		function getSources(sourceInfos) {
			for (var i = 0; i != sourceInfos.length; ++i) {
				var sourceInfo = sourceInfos[i];
				if (sourceInfos[i].kind == 'video') videoSource.push(sourceInfos[i].id);
				if (sourceInfos[i].kind == 'audio') audioSource.push(sourceInfos[i].id);
			}
		};

		function draw(v, ctx, w, h) {
			ctx.drawImage(v, 0, 0, w, h);
		};

		function errorCallback(err) {
			if (CallMe.debug) console.log(err);
		};
	};
	var user = function(id, name, peer, img) {
		var u = this;
		this.id = id;
		this.name = name;
		this.img = 'data:image/png;base64,' + img;
		this.peer_id = peer;
		this.call = null;
		this.preview = $('<img class="img-circle img-circle-thumb" src="' + this.img + '"><span>' + this.name + '</span>');
		this.conn = null;
		this.element = $('<li>' + '<a>' + '<img class="img-circle img-circle-thumb" src="' + this.img + '"><span>' + this.name + '</span><button class="btn btn-default btn-round round-sm disabled"><span class="glyphicon glyphicon-ok"></span></button></a></li>');
		this.element.data().user = this;
		this.connect = function(id, peer_id) {
			u.peer_id = (u.peer_id == peer_id) ? u.peer_id : peer_id;
			if (u.conn == null || !u.conn.open) {
				u.conn = CallMe.peer.connect(u.peer_id);
				u.conn.on('open', function(data) {
					u.conn.send('@system-message:newUserOnline');
					u.element.find('.btn-default').removeClass('btn-default').addClass('btn-success');
					CallMe.users.add();
				});
			}
		};
	}
	this.init = function() {
		this.camera = new camera('#' + ownVideo.attr('id'));
		this.camera.init();
	}
	this.refreshInterval = null;
	this.users = {
		items: [],
		add: function() {
			this.sort();
			usersContainer.find('li a').parent().remove();
			$(this.items).each(function() {
				this.element.data().user = this;
				usersContainer.append(this.element);
			});
		},
		getUser: function(type, val, index) {
			index = typeof index == 'undefined' ? 0 : index;
			return this.items.filter(function(a) {
				return a[type] == val
			})[index];
		},
		notAvailable: function(peer_id) {
			this.getUser('peer_id', peer_id).element.find('button').removeClass('btn-success').addClass('btn-default');
			this.add();
		},
		refresh: function(retVal) {
			for (var i = 0; i < retVal.length; i++) {
				u = new user(retVal[i].id, retVal[i].name, retVal[i].peer_id, retVal[i].img);
				var usr = this.getUser('id', u.id);
				if (usr == null) {
					this.items.push(u);
					u.connect(u.id, u.peer_id);
				} else if (!usr.conn.open) {
					usr.connect(u.id, u.peer_id);
				}
			};
			this.add();
		},
		sort: function() {
			this.items.sort(function(a, b) {
				var A = a.name.toLowerCase();
				var B = b.name.toLowerCase();
				if (A < B) {
					return -1;
				} else if (A > B) {
					return 1;
				} else {
					return 0;
				}
			});
			this.items.sort(function(a, b) {
				var x = (typeof a.conn.open != 'undefined') ? a.conn.open : false;
				var y = (typeof b.conn.open != 'undefined') ? b.conn.open : false;
				if (x > y) {
					return -1;
				} else if (x < y) {
					return 1;
				} else {
					return 0;
				}
			});
		}
	}
	this.Tone = {
		inTone: new Audio(),
		outTone: new Audio(),
		interval: null,
		init: function() {
			this.inTone.src = "snd/ring.mp3";
			this.inTone.loop = true;
			this.outTone.src = "snd/Ringing.mp3";
		},
		inTonePlay: function() {
			this.init();
			this.inTone.play();
		},
		inToneStop: function() {
			this.inTone.pause();
		},
		outTonePlay: function() {
			this.init();
			var _tone = this;
			this.outTone.play();
			this.interval = setInterval(function() {
				_tone.outTone.play();
			}, 4000);
		},
		outToneStop: function() {
			this.outTone.pause();
			clearInterval(this.interval);
		}
	};
	this.peer.on('open', function(data) {
		peerOpen(data);
	});
	this.peer.on('call', function(call) {
		peerCall(call);
	});
	this.peer.on('error', function(err) {
		peerError(err);
	});
	this.peer.on('connection', function(conn) {
		conn.on('open', function() {
			connOpen(this);
			conn.on('data', function(data) {
				connData(data);
			});
		});
	});
	this.peer.on('close', function(data) {
		var call = CallMe.users.getUser('peer_id', data);
		peerCallClose(call);
	});
	this.stopCall = function(data) {
		peerCallClose(data);
	};
	this.newUserOnline = function(data) {
		ajaxCall('com.php', 'getUsers', {}, true);
	}

	function prepareCall(call) {
		if (window.existingCall) {
			window.existingCall.close();
			CallMe.peer.connections[window.existingCall.peer].forEach(function(el, index) {
				if (el.id == window.existingCall.id) {
					CallMe.peer.connections[window.existingCall.peer].splice(index, 1);
					return;
				}
			});
		}
		call.on('stream', function(stream) {
			theirVideo.prop('src', URL.createObjectURL(stream));
			CallMe.Tone.outToneStop();
			callModal.modal('hide');
		});
		window.existingCall = call;
		window.existingCall.on('close', peerCallClose, call);
	}

	function acceptCall(call) {
		prepareCall(call);
		var currentUser = CallMe.users.getUser('peer_id', call.peer);
		currentUser.element.addClass('active');
		previewUser.children().remove();
		previewUser.append(currentUser.preview);
		window.existingCall.answer(window.localStream);
		window.existingCall.on('close', endCall, call);
		CallMe.Tone.inToneStop();
		callModal.modal('hide');
	}

	function peerOpen(data) {
		if (CallMe.debug) console.log(data);
		ajaxCall('com.php', 'setUser', {
			peer_id: data
		}, true);
	}

	function peerCall(call) {
		if (CallMe.debug) console.log(call);
		var currentCall = CallMe.users.getUser('peer_id', call.peer);
		if (currentCall != null) {
			currentCall.call = call;
			startCall.eq(1).data().call = call;
			callModalHandler('show', 'Incoming Call', currentCall.name, currentCall.img, true, true, 'inTonePlay');
		}
	}

	function peerError(err) {
		if (CallMe.debug) console.log(err);
		if (err.type == 'network') {
			clearInterval(CallMe.refreshInterval);
			wrapper.toggleClass("toggled");
			alertBox.find('span').html('  ' + err.message + '<br> Please try latter!');
			alertBox.toggleClass('fade');
			CallMe.users.add();
		}
		if (err.type == 'peer-unavailable') {
			var peer_id = err.message.split(' ')[5];
			CallMe.users.notAvailable(peer_id);
			delete CallMe.peer.connections[peer_id];
		}
	}

	function connOpen(conn) {
		if (CallMe.debug) console.log(conn);
		var user = CallMe.users.getUser('peer_id', conn.peer);
		if (user != null) {
			user.element.find('.btn-default').removeClass('btn-default').addClass('btn-success');
		}
	}

	function connData(data) {
		if (CallMe.debug) console.log('data:' + data);
		if (data.indexOf('@system-message:') != -1) {
			var fn = data.replace('@system-message:', '');
			/*
            var fn = data.replace('@system-message:', '');
            var regExp = /\(([^)]+)\)/;
            var matches = regExp.exec(fn);
            fn = fn.replace(matches[0], '');
            data = matches[1]
             */
			executeFunctionByName(fn, CallMe, data);
		}
	}

	function peerCallClose(data) {
		if (CallMe.debug) console.log(data);
		if (window.existingCall && window.existingCall.open) {
			window.existingCall.close();
		}
		CallMe.Tone.outToneStop();
		CallMe.Tone.inToneStop();
		callModal.modal('hide');
		endCall.addClass('hidden');
		theirVideo.prop('src', '');
	}

	function executeFunctionByName(functionName, context, args) {
		var args = [].slice.call(arguments).splice(2);
		var namespaces = functionName.split(".");
		var func = namespaces.pop();
		for (var i = 0; i < namespaces.length; i++) {
			context = context[namespaces[i]];
		}
		return context[func].apply(this, args);
	}

	function callModalHandler(state, title, body, img, callBt, endBt, tone) {
		callModal.find('.modal-title').text(title);
		callModal.find('.modal-body h3').text(body);
		callModal.find('img').attr('src', img);
		if (callBt) {
			startCall.removeClass('hidden');
		} else {
			startCall.addClass('hidden');
		}
		if (endBt) {
			endCall.removeClass('hidden');
		} else {
			endCall.addClass('hidden');
		}
		callModal.modal(state);
		CallMe.Tone[tone]();
	}

	function toggleFullScreen() {
		var bt = $(event.target || event.srcElement);
		bt.blur();
		if (!$.fullscreen.isFullScreen()) {
			theirVideo.parent().fullscreen();
		} else {
			$.fullscreen.exit();
		}
		return false;
	}

	function ajaxCall(file, order, param, sync, item) {
		typeof sync == 'undefined' ? sync = false : sync = sync;
		typeof item == 'undefined' ? item = null : item = item;
		var retVal;
		var fullrow;
		$.ajax({
			url: file,
			type: 'POST',
			async: sync,
			dataType: 'xml/html/script/json/jsonp',
			data: {
				order: order,
				param: param
			},
			complete: function(data, xhr, textStatus) {
				retVal = $.parseJSON(data.responseText);
				if (order == 'getUsers') {
					CallMe.users.refresh(retVal);
				}
			},
			success: function(data, textStatus, xhr) {
				retVal = $.parseJSON(data.responseText);
			},
			error: function(xhr, textStatus, errorThrown) {}
		});
		return retVal;
	}

}).call(window.CallMe = window.CallMe || {}, jQuery);
CallMe.init();