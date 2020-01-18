var config = {

	map: {
		'*': {
			'alothemes': 'Magiccart_Alothemes/js/alothemes',
		},
	},

	paths: {
		'magiccart/easing'		: 'Magiccart_Alothemes/js/plugins/jquery.easing.min',
		'magiccart/parallax'		: 'Magiccart_Alothemes/js/plugins/jquery.parallax',
		'magiccart/bootstrap'		: 'Magiccart_Alothemes/js/plugins/bootstrap.min',
		'magiccart/fancybox'		: 'Magiccart_Alothemes/js/plugins/jquery.fancybox.pack',
		'magiccart/socialstream'	: 'Magiccart_Alothemes/js/plugins/jquery.socialstream',
		'magiccart/slick'			: 'Magiccart_Alothemes/js/plugins/slick.min',
		'magiccart/zoom'			: 'Magiccart_Alothemes/js/plugins/jquery.zoom.min',
		// 'alothemes'		: 'Magiccart_Alothemes/js/alothemes',
	},

	shim: {
		'magiccart/easing': {
			deps: ['jquery']
		},
		'magiccart/parallax': {
			deps: ['jquery']
		},
		'magiccart/bootstrap': {
			deps: ['jquery']
		},
		'magiccart/fancybox': {
			deps: ['jquery']
		},
		'magiccart/socialstream': {
			deps: ['jquery']
		},
		'magiccart/slick': {
			deps: ['jquery']
		},
		'magiccart/zoom': {
			deps: ['jquery']
		},
        'alothemes': {
            deps: ['jquery', 'magiccart/easing', 'magiccart/slick' , 'magiccart/zoom']
        },

	}

};
