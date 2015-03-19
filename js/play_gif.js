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
jQuery(function(){
    //gifs HAVE to be preloaded, otherwise nothing happens for ages when user clicks play!
    var gifs = []; //Array that will hold all gifs as Images
    var gif_urls = [];//array holds all GIF Urls
    var player_wrap = jQuery('.gif_wrap');
    var showing_btn = jQuery('span.play_gif'); //Play "Button" - purely css
    var playing = false;
    var first_load = true;
    var stop_load = false;
    var last_viewed = null; //index of gif that was last played

    //first spinner which is shown before the window has fully loaded
    var spinnerPreload;
    var spinnerLoading;
    var spinnerOptions = {
        lines: 13,
        length: 12,
        width: 8,
        radius: 18,
        trail: 100,
        speed: 1.3,
        color: '#fff',
        className: 'gif_spinner'
    };
    spinnerPreload = new Spinner(spinnerOptions);
    spinnerLoading = new Spinner(spinnerOptions);

    //Preloads one gif as an image object
    function preload_gif(url, idx){
        if(!gifs[idx]){//if there not already an image at that idx in gifs, create one
            var img = new Image();
            img.src = url;
            img.className = '_showing';
            gifs[idx] = img;
            first_load = true;
            return img;
        }else {
            first_load = false;
            return gifs[idx];
        }
    }

    //Save all GIF urls
    jQuery('._showing.frame').each(function(){
        var s = jQuery(this);
        if(s.attr('src')){
            if(s.attr('data-lazy-src')){//unfassbar.es -> lazy load enabled
                gif_urls.push(s.attr('data-lazy-src').replace('_still_tmp.jpeg', '.gif'));
            }else{
                if(typeof s.attr('src') !== 'undefined' && s.attr('src') !== false){
                    gif_urls.push(s.attr('src').replace('_still_tmp.jpeg', '.gif'));
                } else {
                    gif_urls.push(s.src.replace('_still_tmp.jpeg', '.gif'));
                }
            }
        } else if ( s.data('cfsrc') ) { //CloudFlare sets the "src" as 'data-cfsrc="..."'
            gif_urls.push(s.data('cfsrc').replace('_still_tmp.jpeg', '.gif'));
        }
    });

    jQuery(document).ready( function () {
        //start preloading spinner
        spinnerPreload.spin();//start Spinner
        if(jQuery('.gif_wrap').length){
            jQuery('.gif_wrap')[0].appendChild(spinnerPreload.el);//only show on first gif (if we wanted to show it on all gifs, we'd have to instantiate a new spinner for each)
        }
    });


    jQuery(window).load( function() {
        spinnerPreload.stop(); //stop spinner when all images have loaded and play button should be clickable

        var showing_btn_idx = "";
        var gif_img;
        var displayedImgSrc;
        var hiddenImgSrc;

        //Button is hidden before whole DOM tree is loaded, otherwise it jumps from top to center of .gif_wrap
        showing_btn.css('visibility', 'visible'); //show GIF Play Button

        function play(idx){
            //Img / GIF sources
            displayedImgSrc = jQuery('._showing')[idx].src;
            hiddenImgSrc =  jQuery('._hidden')[idx].src;
            //Index of last played element
            last_viewed = idx;
            showing_btn_idx = showing_btn[idx]; //specific GIF Button for this clicked element
            if(playing == false){ //hide first frame and GIF button
                playing = true;
                showing_btn_idx.style.visibility = 'hidden';
            }else{ //display first frame and GIF button
                playing = false;
                showing_btn_idx.style.visibility = 'visible';
            }


            if(displayedImgSrc == gifs[idx].src && !first_load){ //if the gif is already showing
                displayedImgSrc = hiddenImgSrc;
                hiddenImgSrc = gifs[idx].src;

                if(playing == false) //if the the gif that was played is clicked again and stops last_view has to be set to null, otherwise two gifs start at the same time
                    last_viewed = null;
                else
                    last_viewed = idx;
            }else if(hiddenImgSrc == gifs[idx].src){ // if still is showing
                hiddenImgSrc = displayedImgSrc;
                displayedImgSrc =  gifs[idx].src;

            }
            jQuery('._showing')[idx].src = displayedImgSrc;
            jQuery('._hidden')[idx].src = hiddenImgSrc;
        }

        jQuery('.gif_wrap').click( function(event){
            var self = this;
            var idx = jQuery('.gif_wrap').index(this); //returns index of clicked div

            if(!gifs[idx]) {
                first_load = true;
                //target is gif_wrap
                spinnerLoading.spin();//start Spinner
                self.appendChild(spinnerLoading.el);
            }
            //This is to check if the user clicked again before the gif was fully loaded.
            //If so, we need to stop the onload Event for the image by setting first_load to false.
            //Preload the gif onclick
            if(first_load){
                stop_load = true;
                gif_img = preload_gif(gif_urls[idx], idx);
                gif_img.onload = function(){ //could possibly cause errors (asynch. http://stackoverflow.com/questions/20613984/jquery-or-javascript-check-if-image-loaded)
                    spinnerLoading.stop();
                    first_load = false; //set first_load to false, otherwise, if a gif is clicked twice the src of the still is overwritten.
                };

                //append gif as img src
                jQuery(self).children('img').attr('src', gif_img.src);
            }

            if(!last_viewed && last_viewed != 0){
                last_viewed = idx; //last_viewed != 0 has to be included because !0 is true
            }
            if(idx == last_viewed){ //the index of the gif_wrap element that's just been clicked is the same as idx of last click
                play(idx);
            }else { //idx of element that's just been clicked differs from element that's last been clicked
                if(playing){
                    showing_btn_idx = showing_btn[last_viewed]; //play button of last played gif
                    showing_btn_idx.style.visibility = 'visible';
                    if(jQuery('._showing')[last_viewed].src == gifs[last_viewed].src){ //if the gif is already showing
                        var tmpSrc = jQuery('._showing')[last_viewed].src;
                        jQuery('._showing')[last_viewed].src = jQuery('._hidden')[last_viewed].src;
                        jQuery('._hidden')[last_viewed].src = tmpSrc;
                    }
                    playing = false;
                    play(idx);
                }
            }
        });
        player_wrap.mouseenter( function(){ player_wrap.css('cursor', 'pointer'); } ); //change mouse on enter, when leaving mouse changes on default
    });
});