(function() {
	window.im4wp = window.im4wp || {
		listeners: [],
		forms: {
			on: function(evt, cb) {
				window.im4wp.listeners.push(
					{
						event   : evt,
						callback: cb
					}
				);
			}
		}
	}
})();
