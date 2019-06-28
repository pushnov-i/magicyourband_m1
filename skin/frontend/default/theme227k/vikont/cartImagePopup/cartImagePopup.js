if (typeof CartImagePopup == 'undefined') {

CartImagePopup = function() {return function($) { return {
	init: function() {
		$('.vkCartImagePopup-thumb > img').click(this.show);
		$('#vkCartImagePopup-imgBox-close').click(this.hide);
		$('#vkCartImagePopup-overlay').click(this.hide);
		$('#vkCartImagePopup-imgBox').click(this.hide);
		return this;
	},
	show: function(event) {
		$('#vkCartImagePopup-overlay').show();
		$('#vkCartImagePopup-imgBox').show();
		var source = event.target || event.srcElement;
		$('#vkCartImagePopup-img').attr('src', $(source).attr('fullImgSrc'));
		return this;
	},
	hide: function() {
		$('#vkCartImagePopup-imgBox').hide();
		$('#vkCartImagePopup-overlay').hide();
		return this;
	}
};}(jQuery);};

}
