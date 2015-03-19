<?php
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
if( !defined('WP_UNINSTALL_PLUGIN') ){
    exit();
}
//Delete options
$option_name = 'set_still_as_featured';
delete_option($option_name);

//Delete _first_frame entries in wp_postmeta
global $wpdb;
$num_rows = $wpdb->delete( 'wp_postmeta', array( 'meta_key' => '_first_frame' ) );