/*
	Quotes Plugin - by Jürgen Höfs - www.dream-a-bit.de
	Adds two buttons for creating German (&raquo; / &laquo;) and France (&bdquo; / &rdquo) quotes;
*/
(function() {

	tinymce.create("tinymce.plugins.Quotes", {

		init : function(editor, url) {

			/* France quotes */
			editor.addCommand("mceFranceQuotes", function() {
				var sel = editor.selection;
				var node = sel.getNode();

				var chr_start = '&raquo;';
				var chr_end = '&laquo;';

				if (node) {
					var dom = editor.dom;
					var content = sel.getContent().replace(/<p>/ig, '').replace(/<\/p>/ig, ' ').replace(/[\s]+$/g, '');
					if (content) {
						sel.setContent(chr_start + content + chr_end + ' ');
					} else {
						sel.setContent(chr_start + '<span id="remove_me"></span>' + chr_end);
						sel.select(sel.dom.select('span#remove_me')[0]);
						sel.dom.remove(sel.dom.select('span#remove_me')[0]);
					}
					editor.nodeChanged();
				}
			});
			editor.addButton("francequotes", {
				title: "France Quotes",
				image: url + "/francequotes.gif",
				cmd: "mceFranceQuotes"

			});

			/* Swiss quotes */
			editor.addCommand("mceSwissQuotes", function() {
				var sel = editor.selection;
				var node = sel.getNode();

				var chr_start = '&laquo;';
				var chr_end = '&raquo;';

				if (node) {
					var dom = editor.dom;
					var content = sel.getContent().replace(/<p>/ig, '').replace(/<\/p>/ig, ' ').replace(/[\s]+$/g, '');
					if (content) {
						sel.setContent(chr_start + content + chr_end + ' ');
					} else {
						sel.setContent(chr_start + '<span id="remove_me"></span>' + chr_end);
						sel.select(sel.dom.select('span#remove_me')[0]);
						sel.dom.remove(sel.dom.select('span#remove_me')[0]);
					}
					editor.nodeChanged();
				}
			});
			editor.addButton("swissquotes", {
				title: "Swiss Quotes",
				image: url + "/swissquotes.gif",
				cmd: "mceSwissQuotes"

			});

			/* German Quotes */
			editor.addCommand("mceGermanQuotes", function() {
				var sel = editor.selection;
				var node = sel.getNode();

				var chr_start = '&bdquo;';
				var chr_end = '&rdquo;';

				if (node) {
					var dom = editor.dom;
					var content = sel.getContent().replace(/<p>/ig, '').replace(/<\/p>/ig, ' ').replace(/[\s]+$/g, '');
					if (content) {
						sel.setContent(chr_start + content + chr_end + ' ');
					} else {
						sel.setContent(chr_start + '<span id="remove_me"></span>' + chr_end);
						sel.select(sel.dom.select('span#remove_me')[0]);
						sel.dom.remove(sel.dom.select('span#remove_me')[0]);
					}
					editor.nodeChanged();
				}
			});
			editor.addButton("germanquotes", {
				title: "German Quotes",
				image: url + "/germanquotes.gif",
				cmd: "mceGermanQuotes"

			});


		}
	});

	tinymce.PluginManager.add("quotes", tinymce.plugins.Quotes);

})();
