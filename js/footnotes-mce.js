/* 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/
(function() {
    var { __ } = wp.i18n;
    tinymce.PluginManager.add('footnotes-made-easy', function( editor, url ) {
        editor.addButton( 'footnotes-made-easy', {
            title: __( 'Add / remove footnote', 'footnotes-made-easy' ),
            icon: 'footnotes-made-easy-admin-button',
            onclick: function() {
                //if text is highlighted, wrap that text in a footnote
                //otherwise, show an editor to insert a footnote
                editor.focus();
                var content = editor.selection.getContent();
                if (content.length > 0) {
                    if (content.indexOf(fme_gut.open) == -1 && content.indexOf(fme_gut.close) == -1) {
                        editor.selection.setContent(fme_gut.open + content + fme_gut.close);
                    } else if (content.indexOf(fme_gut.open) != -1 && content.indexOf(fme_gut.close) != -1) {
                        editor.selection.setContent(content.replace(fme_gut.open, '').replace(fme_gut.close, ''));
                    } else {
                        //we don't have a full tag in the selection, do nothing
                    }
                } else {
                    editor.windowManager.open( {
                        title: __( 'Insert Footnote', 'footnotes-made-easy' ),
                        body: [{
                            type: 'textbox',
                            name: 'footnote',
                            label: __( 'Foot note', 'footnotes-made-easy' ),
                        }],
                        onsubmit: function( e ) {
                            editor.insertContent( fme_gut.open + e.data.footnote + fme_gut.close);
                        }
                    });
                }
            }
    
        });
    });
    })();