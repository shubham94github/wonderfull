jQuery(document).ready(function(e) {
    // clipboard for report
    e("#ddd-success-report").hide(),
        new Clipboard("#ddd-copy-report").on("success", function() {
            e("#ddd-success-report").show()
        });
    jQuery('table.ddd-report-table td a, table.ddd-report-table th a').on('click touch', function() {
        event.preventDefault();
    });
});

jQuery(document).ready(function($) {
    // main function
    function onIframeLoad() {
        setTimeout(function() {
            $('iframe#ondemanIframe').on('load', function() {
                var frame = document.getElementById('ondemanIframe');

                // function to chech active assistant plugins
                // @plugin_name - plugin name to check
                function send_plugin_status(plugin_name) {
                    jQuery.ajax({
                        type: 'GET',
                        url: ajaxurl,
                        data: 'action=ddd_get_plugin_activation_state&plugin_name=' + plugin_name,
                        success: function(data) {
                           //console.log(data);
                            if (data === 'Activated') {
                                frame.contentWindow.postMessage(plugin_name + '_activated', '*');
                            } else {
                                frame.contentWindow.postMessage(plugin_name + '_deactivated', '*');
                            }
                        },
                        error: function(data) {
                            console.log(data);
                        }
                    });
                }
                // remove divi loading animation
                $('#et_pb_loading_animation').remove();

                // check activated assistat plugins
                send_plugin_status('pixie');
                send_plugin_status('mermaid');
                send_plugin_status('unicorn');
                send_plugin_status('3d_portfolio');
                send_plugin_status('jackson');
                send_plugin_status('mozart');
                send_plugin_status('falkor');
                send_plugin_status('pegasus');
                send_plugin_status('venus');
                send_plugin_status('sigmund');
                send_plugin_status('coco');
                send_plugin_status('jamie');
                send_plugin_status('impi');

                // function to get post id from the url parameter 'post'
                function getUrlVars() {
                    var vars = [],
                        hash;
                    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
                    for (var i = 0; i < hashes.length; i++) {
                        hash = hashes[i].split('=');
                        vars.push(hash[0]);
                        vars[hash[0]] = hash[1];
                    }
                    return vars;
                }

                var post_id = getUrlVars()["post"];

                // Create IE + others compatible event handler
                var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
                var eventer = window[eventMethod];
                var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

                // Listen to message from child window
                eventer(messageEvent, function(e) {
                    // console.log(e.origin);
                    if (e.origin === 'https://ondemand.divi-den.com') {
                        var response;
                        //console.log(jQuery.type(e.data));
                        //console.log('parent received message!:  ', e.data);
                        if (jQuery.type(e.data) === 'string') { // check if the response is text

                            if (~e.data.indexOf('context')) { // if the response is a divi json file
                                $('body .ddd-assistant .saving_message').show();
                                var ddd_replace_content = 'off';

                                if (jQuery('.ddd-replace-content input').attr("checked") === 'checked') {
                                    ddd_replace_content = 'on';
                                }
                                response = jQuery.parseJSON(e.data);
                                //console.log('response: ' + JSON.stringify(response, null, 4));
                                if (response) {
                                    if (!$(frame).hasClass('settingsIframe') && response.context == 'et_builder') {
                                        console.log('LOADING LAYOUT');
                                        layout = JSON.stringify(response);
                                        var ddd_list = jQuery('.et-pb-all-modules-tab .et-pb-load-layouts');
                                        //console.log(ddd_list.html());
                                        var ddd_li = ddd_list.children('li').last().clone(true);

                                        // console.log('ddd_li ' + ddd_li.html());

                                        ddd_li.css('background-color', 'red');
                                        ddd_li.addClass('layout_here');
                                        ddd_li.appendTo(ddd_list);

                                        jQuery('.layout_here').data('layout_id', { layout: layout, replace_content: ddd_replace_content });
                                        jQuery('.layout_here .et_pb_layout_button_load').click();
                                    } // if(response.context == 'et_builder') {
                                    else if (response.context == 'et_builder_layouts' || $(frame).hasClass('settingsIframe')) {

                                        console.log('SAVING TO LIBRARY');
                                        //console.log('action=ddd_import_posts&posts=' + JSON.stringify(response.data));
                                          response_data = encodeURIComponent(JSON.stringify(response));
                                       // console.log('response_data: ' + response_data);
                                        //console.log(response.data);
                                        // import to library
                                        jQuery.ajax({
                                            type: 'POST',
                                            url: ajaxurl,
                                            // processData: false,
                                            data: 'action=ddd_import_posts&posts=' + response_data,
                                            success: function(data) {
                                                //console.log(data);
                                                $('.ddd-tab-section').html('<h3 class="sectionSaved">Your section saved to the Divi Library. Please save this page and go to "Add from library" tab to add the saved section to the page.</h3>');
                                                $('body .ddd-assistant .saving_message').hide();
                                                $('body .ddd-assistant .loaded_message').show();
                                                setTimeout(function() {
                                                    $('body .ddd-assistant .loaded_message').hide();
                                                  }, 5500);
                                            },
                                            error: function(data) {
                                                console.log(data);
                                            }
                                        });

                                    } // if(response.context == 'et_builder_layouts')
                                } //  if (response)
                            } // if (~e.data.indexOf('context'))
                            else if (~e.data.indexOf('.')) { // if the response is a css file
                                // console.log('CSS');
                                // console.log(e.data);
                                $('input#_et_pb_custom_css').val(e.data);
                            }

                        } //if jQuery.type(e.data) === 'string'
                    } //if (e.origin === 'https://ondemand.divi-den.com') {
                }, false);


            });
        }, 200);
    }
    //function onIframeLoad()

    $('body .ddd-assistant .loaded_message span.close').on('click', function() {
        $('body .ddd-assistant .loaded_message').hide();
    });

    // isert Divi Den on Demand Tabs to Divi builder
    jQuery.ajax({
        type: 'GET',
        url: ajaxurl,
        data: 'action=ddd_get_option',
        success: function(data) {
            var ddd_enable = data + '';
            // console.log(ddd_enable);
            if (ddd_enable === 'enabled') { // check if the DDD is enabled in settings

                onIframeLoad(); // our main function

                // Insert layout from library
                $(document).on('mouseup', '.et-pb-layout-buttons-load', function() {
                    setTimeout(function() {

                        var tabbar = $('.et-pb-saved-modules-switcher');
                        if (tabbar.length) {
                            $('li.et-pb-options-tabs-links-active').removeClass('et-pb-options-tabs-links-active');
                             $('div.active-container').removeClass('active-container').css('opacity', 0);
                            tabbar.prepend('<li class="ddd et-pb-options-tabs-links-active" data-open_tab="ddd-tab" data-layout_type="layout"><a href="#"><img height="25" src="'+ddd_path_to_plugin+'/include/ddd-icon.png" /> <span>Divi Den on Demand</span></a></li>');
                            $(".et_pb_modal_settings").append('<div class="et-pb-main-settings et-pb-main-settings-full ddd-tab ddd-tab-layout active-container" style="opacity: 1;"><div class="et-dlib-load-options ddd-replace-content et-fb-checkboxes-category-wrap"><p><label><input type="checkbox" value="replace_content" checked="checked">Replace existing content</label></p></div><iframe id="ondemanIframe" name="ondemandIframe" class="layoutsIframe" src="https://ondemand.divi-den.com/"></iframe></div>');
                        }

                    }, 200);

                    onIframeLoad();

                });

                //Insert section from library
                $(document).on('mouseup', '.et-pb-section-add-saved', function() {
                    setTimeout(function() {

                        jQuery('.et_pb_modal_settings.et_pb_modal_no_tabs').removeClass('et_pb_modal_no_tabs');

                         $('li.et-pb-options-tabs-links-active').removeClass('et-pb-options-tabs-links-active');
                        $('div.active-container').removeClass('active-container').css('opacity', 0);

                        jQuery('.et_pb_modal_settings_container h3').after(' \
                    <ul class="et-pb-options-tabs-links et-pb-saved-modules-switcher">  \
                    <li class="ddd" data-open_tab="ddd-tab" data-layout_type="section"><a href="#"><img height="25" src='+ddd_path_to_plugin+'/include/ddd-icon.png" /> <span>Divi Den on Demand</span></a></li> \
                        <li class="et-pb-saved-module" data-open_tab="et-pb-saved-modules-tab" > \
                            <a href="#">Add From Library</a>    \
                        </li>   \
                    </ul>   \
                    <div class="et-pb-main-settings et-pb-main-settings-full ddd-tab ddd-tab-section active-container" style="opacity: 1;display:block !important;" ><iframe id="ondemanIframe" name="ondemandIframe" class="sectionsIframe" src="https://ondemand.divi-den.com/sections-search/"></iframe></div> \
                ');

                    }, 200);

                    onIframeLoad();
                });


            } //if (ddd_enable == 'enabled')
        },
        error: function(data) {
            console.log(data);
        }
    });

    // Yes - No button UI
    $('.ddd-assistant .yes_no_button').each(function() {
        var $checkbox = $(this);
        var value = $checkbox.is(':checked');
        var state = value ? 'et_pb_on_state' : 'et_pb_off_state';
        var $template = $($('#epanel-yes-no-button-template').html()).find('.et_pb_yes_no_button').addClass(state);

        $checkbox.hide().after($template);

        if ('et_pb_static_css_file' === $checkbox.attr('id')) {
            $checkbox
                .parent()
                .addClass(state)
                .next()
                .addClass('et_pb_clear_static_css')
                .on('click', function() {
                    epanel_clear_static_css(false, true);
                });

            if (!value) {
                $checkbox.parents('.et-epanel-box').next().hide();
            }
        }

    });

    // Enable / Disable DDD button
    $('.ddd-assistant .et-box-content').on('click', '.et_pb_yes_no_button', function(e) {
        e.preventDefault();

        var $click_area = $(this),
            $box_content = $click_area.parents('.et-box-content'),
            $checkbox = $box_content.find('input[type="checkbox"]'),
            $state = $box_content.find('.et_pb_yes_no_button');

        $ddd_option = $box_content.find('input').attr('name');

        $state.toggleClass('et_pb_on_state et_pb_off_state');

        if ($checkbox.is(':checked')) {
            $checkbox.prop('checked', false);
        } else {
            $checkbox.prop('checked', true);
        }

        if ($click_area.hasClass('et_pb_on_state')) {
            ajax_value = 'enabled';
            $('<iframe id="ondemanIframe" name="ondemandIframe" src="https://ondemand.divi-den.com/"></iframe>').insertAfter('.ddd-assistant hr');
            onIframeLoad();
        } else {
            ajax_value = 'disabled';
            $('.ddd-assistant iframe#ondemanIframe').remove();
        }

        // update DDD enable / disable option
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: 'action=ddd_update_option&ddd_option=' + $ddd_option + '&ddd_option_val=' + ajax_value,
            success: function(data) {
                // console.log(data);
            },
            error: function(data) {
                console.log(data);
            }
        });

    });

    $('.ddd-accordion .ddd-accordion-header').click(function() {
        //Expand or collapse this panel
        $(this).next('.ddd-accordion-content').slideToggle('fast');
        $(this).parent('.ddd-accordion').toggleClass('closed').toggleClass('opened');

        $('.ddd-accordion.opened h3 span').html('-');
        $('.ddd-accordion.closed h3 span').html('+');

        //Hide the other panels
        //  $(".ddd-accordion-content").not($(this).next('.ddd-accordion-content')).slideUp('fast');

    });

}); //jQuery(document).ready(function($)