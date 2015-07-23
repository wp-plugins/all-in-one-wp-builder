(function() {
	tinymce.PluginManager.add('ve_popup', function( editor, url ) {
		var sh_tag = 've_popup';

		//helper functions 
		function getAttr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ?  window.decodeURIComponent(n[1]) : '';
		};



		//add popup
		editor.addCommand('ve_insert_popup', function(ui, v) {
			//setup defaults
			var id = '';
			if (v.id)
                id = v.id;
			var position = '';
			if (v.align)
                position = v.position;
			var exclass = '';
			if (v.exclass)
                exclass = v.exclass;
            var style = '';
            if (v.style)
                exclass = v.style;
			editor.windowManager.open( {
				title: 'Ve Popup Shortcode',
				body: [
					{
						type: 'listbox',
						name: 'id',
						label: 'Select Popup',
						value: id,
						tooltip: 'Select popup you want',
                        'values': ve_popups
					},

					{
						type: 'listbox',
						name: 'position',
						label: 'Position',
						value: position,
						'values': [
                            {text: 'Default', value: ''},
							{text: 'Center', value: 'center'},
							{text: 'Top left', value: 'top-left'},
							{text: 'Top Right', value: 'top-right'},
							{text: 'Bottom left', value: 'bottom-left'},
                            {text: 'Bottom Right', value: 'bottom-right'}
						],
						tooltip: 'Select widget alignment'
					},
                    {
                        type: 'textbox',
                        name: 'style',
                        label: 'Custom Style',
                        value: style,
                        multiline: false
                    },
					{
						type: 'textbox',
						name: 'exclass',
						label: 'Extra Class',
						value: exclass,
						multiline: false
					}
				],
				onsubmit: function( e ) {
                    if(e.data.id == "")
                    {
                        e.data.id = ve_popups[0].value;
                    }

					var shortcode_str = '[' + sh_tag + ' id="'+e.data.id+'"';
                    if(e.data.exclass != "") {
                        shortcode_str += ' class="' + e.data.exclass + '"';
                    }
                    if(e.data.position != "") {
                        shortcode_str += ' position="' + e.data.position + '"';
                    }
                    if(e.data.style != "") {
                        shortcode_str += ' style="' + e.data.style + '"';
                    }
					shortcode_str += ']';
					//insert shortcode to tinymce
					editor.insertContent( shortcode_str);
				}
			});
	      	});

		//add button
		editor.addButton('ve_popup', {
			icon: 've_popup',
			tooltip: 'Ve Popup',
			onclick: function() {
				editor.execCommand('ve_insert_popup','',{
					header : '',
					footer : '',
					type   : 'default',
					content: ''
				});
			}
		});



		//open popup on placeholder double click
		editor.on('DblClick',function(e) {
			var cls  = e.target.className.indexOf('wp-bs3_panel');
			if ( e.target.nodeName == 'IMG' && e.target.className.indexOf('wp-bs3_panel') > -1 ) {
				var title = e.target.attributes['data-sh-attr'].value;
				title = window.decodeURIComponent(title);
				var content = e.target.attributes['data-sh-content'].value;
				editor.execCommand('bs3_panel_popup','',{
					header : getAttr(title,'header'),
					footer : getAttr(title,'footer'),
					type   : getAttr(title,'type'),
					content: content
				});
			}
		});
	});
})();