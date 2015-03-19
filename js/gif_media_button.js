/*
 WP Gif Player, an easy to use GIF Player for Wordpress
 Copyright (C) 2015  Stefanie Stoppel @ psmedia GmbH (http://p-s-media.de/kontakt)

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
jQuery(function($) {
    $(document).ready(function(){
        $('#wpgp-insert-gif').click(open_media_window);
    });

    function open_media_window() {
        if (this.window === undefined) {
            this.window = wp.media({
                title: 'Insert a media',
                library: {type: 'image'},
                multiple: true,
                button: {text: 'Insert'}
            });

            var self = this; // Needed to retrieve our variable in the anonymous function below
            this.window.on('select', function() {
                var all_gifs = self.window.state().get('selection').toJSON();
                $(all_gifs).each(function(){
                    if(this.mime === "image/gif"){
                        wp.media.editor.insert('[WPGP gif_id="' + this.id + '" width="600"]\n');
                    }else{
                        alert(unescape("Der hochgeladene Dateityp wird von WP Gif Player nicht unterst%FCtzt. Bitte laden Sie nur Dateien im .gif Format hoch%21%0A"));
                    }
                });
            });
        }

        this.window.open();
        return false;
    }
});