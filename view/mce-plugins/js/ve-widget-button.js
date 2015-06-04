(function() {
	tinymce.PluginManager.add('ve_widget', function( editor, url ) {
		var sh_tag = 've_widget';

		//helper functions 
		function getAttr(s, n) {
			n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
			return n ?  window.decodeURIComponent(n[1]) : '';
		};



		//add popup
		editor.addCommand('bs3_panel_popup', function(ui, v) {
			//setup defaults
			var id = '';
			if (v.id)
                id = v.id;
			var align = '';
			if (v.align)
                align = v.align;
			var exclass = '';
			if (v.exclass)
                exclass = v.exclass;
            var style = '';
            if (v.style)
                exclass = v.style;
			editor.windowManager.open( {
				title: 'Ve Widget Shortcode',
				body: [
					{
						type: 'listbox',
						name: 'id',
						label: 'Select Widget',
						value: id,
						tooltip: 'Select widget you want',
                        'values': ve_widgets
					},

					{
						type: 'listbox',
						name: 'align',
						label: 'Align',
						value: align,
						'values': [
							{text: 'Default', value: ''},
							{text: 'Left', value: 'left'},
							{text: 'Right', value: 'right'},
							{text: 'Center', value: 'center'}
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
                        e.data.id = ve_widgets[0].value;
                    }

					var shortcode_str = '[' + sh_tag + ' id="'+e.data.id+'"';
                    if(e.data.exclass != "") {
                        shortcode_str += ' class="' + e.data.exclass + '"';
                    }
                    if(e.data.align != "") {
                        shortcode_str += ' align="' + e.data.align + '"';
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
		editor.addButton('ve_widget', {
			icon: 've_widget',
			tooltip: 'Ve Widget',
			onclick: function() {
				editor.execCommand('bs3_panel_popup','',{
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