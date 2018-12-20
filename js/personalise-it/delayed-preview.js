var PersonaliseIt = PersonaliseIt || {};

PersonaliseIt.DelayedPreview = (function() {
	function DelayedPreview(url, id, origin) {
		this.url = url;
		this.id = id;
		this.origin = origin;
		
		this.textFields = {};
		this.busyCount = 1;
			
		window.addEventListener("message", function(e) {			
			if(e.origin === origin && e.data.id === id) {				
				switch(e.data.name) {
					case 'SHOW_BUSY': this.busyCount++; break;
						
					case 'HIDE_BUSY':			// fall through
					case 'RENDERER_READY': 
						this.busyCount--; break;
				}
			}
		});
	}
	
	DelayedPreview.prototype.addTextField = function(optionId, areaId) {
		this.textFields[optionId] = areaId;
	};
	
	DelayedPreview.prototype.update = function(iframeWindow) {
		for(var optionId in this.textFields) {				
			var element = $$('#' + optionId)[0];

			if(element) {
				var text = element.options && element.selectedIndex
							? element.options[element.selectedIndex].text
							: element.value;
						
				iframeWindow.postMessage({
					id: this.id,
					name: 'SET_TEXT_AREA_TEXT',
							
					body: {
						areaId: this.textFields[optionId],
						text: text
					}
				}, this.origin);
			}
		}
	};
			
	DelayedPreview.prototype.preview = function() {
		this.busyCount = 0;

		var int = 0;
		
		var popup = new Window({
			id:'preview',
			className: 'magento',
			url: this.url,
					
			width: 820,
			height: 600,
					
			minimizable: false,
			maximizable: false,
					
			showEffectOptions: {
				duration: 0.4
			},
					
			hideEffectOptions:{
				duration: 0.4
			},
					
			onload: (function(a,b) {
				var window = $$('#preview_content')[0].contentWindow;

				if(int) {
					clearInterval(int);
				} else {
					int = setInterval((function() {
						if(this.busyCount === 0) {
							clearInterval(int);
								
							this.update(window);
						}					
					}).bind(this), 100);						
				}
			}).bind(this),
					
			destroyOnClose: true
		});
			
		popup.setZIndex(100);
		popup.showCenter(true);
	};
		
	return DelayedPreview;
})();
