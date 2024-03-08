var $doc = jQuery(document),
    $window = jQuery(window);

$window.on('load', function () {

    jQuery('.widgets-chooser-sidebars').find('li').each(function () {
        var $thisElem = jQuery(this),
            thistext = $thisElem.text();

        if (/^Page #\d+ - Section #\d+$/.test(thistext)) {
            $thisElem.addClass('fme-chooser-section').hide();
        }
    });


    /* PAGE TEMPLATES OPTIONS
    ------------------------------------------------------------------------------------------*/
    var $pageTemplateAttr = "select[name='page_template']";
    if (jQuery('body').hasClass('block-editor-page')) {
        $pageTemplateAttr = '.editor-page-attributes__template select';
    }

    var selected_item = jQuery($pageTemplateAttr).val();

    if (selected_item == 'template-authors.php') {
        jQuery('#fme-page-template-authors').show();
    }

    jQuery($pageTemplateAttr).change(function () {
        var selected_item = jQuery($pageTemplateAttr).val();
        jQuery('.fme-page-templates-options').hide();
        if (selected_item == 'template-authors.php') {
            jQuery('#fme-page-template-authors').show();
        }
    });

});

function attachAllDynamicSelects() {
    /** Make Custom Posts sortable and They are using AJAX calls to extract info */

    jQuery('.fme-custom-posts-selector select').select2();
    jQuery('.fme-custom-posts-selector select').select2({
        width: 'resolve',
        ajax: {
            url: ajaxurl, // AJAX URL is predefined in WordPress admin
            dataType: 'json',
            delay: 250, // delay in ms while typing when to perform a AJAX search
            data: function (params) {
                let classList = jQuery('[class^="visual-option"].selected').attr("class");

                // Creating class array by splitting class list string
                let classArr = classList.split(/\s+/);
                let activeBlock = '';
                jQuery.each(classArr, function (index, value) {
                    if (value.startsWith("visual-option-")) {
                        activeBlock = value.split("-").pop();
                    }
                });


                return {
                    q: params.term, // search query
                    block: activeBlock,
                    action: 'fme_get_posts' // AJAX action for admin-ajax.php
                };
            },
            processResults: function (data) {
                var options = [];
                if (data) {

                    // data is the array of arrays, and each of them contains ID and the Label of the option
                    jQuery.each(data, function (index, text) { // do not forget that "index" is just auto incremented value
                        options.push({ id: text[0], text: text[1] });
                    });

                }
                return {
                    results: options
                };
            },
            cache: true
        },
        minimumInputLength: 3 // the minimum of symbols to input before perform a search
    });
    jQuery('.fme-custom-posts-selector select').each(function (index) {
        var selectEl = jQuery(this);
        selectEl.next().children().children().children().sortable({
            containment: 'parent', stop: function (event, ui) {
                ui.item.parent().children('[title]').each(function () {
                    var title = jQuery(this).attr('title');
                    var original = jQuery('option:contains(' + title + ')', selectEl).first();
                    original.detach();
                    selectEl.append(original)
                });
                selectEl.change();
            }
        });
    });
    /** End Custom Posts sortable and They are using AJAX calls to extract info */

}

function attachAllDynamicPostTypes() {
    jQuery('.fme-custom-post-selector input:not([type=hidden])').autocomplete({

        source: function (request, response) {
            jQuery.ajax({
                url: ajaxurl,
                dataType: "json",
                data: {
                    q: request.term, // search query
                    action: 'fme_get_posts', // AJAX action for admin-ajax.php
                    type: 'input'
                },
                success: function (data) {
                    console.log(data);
                    response(data);
                }
            });
        },
        select: function (event, ui) {
            console.log(jQuery(this));
            jQuery(this).parent().find('input:hidden:first').val(ui.item.id);
        },
        minLength: 3,
    });
}

$doc.ready(function () {

    var $figaroBody = jQuery('body');

    /* DASHBORED COLOR
    ------------------------------------------------------------------------------------------ */
    var brandColor = '#d54e21';
    if ($figaroBody.hasClass('admin-color-blue')) {
        brandColor = '#e1a948';
    }
    else if ($figaroBody.hasClass('admin-color-coffee')) {
        brandColor = '#9ea476';
    }
    else if ($figaroBody.hasClass('admin-color-ectoplasm')) {
        brandColor = '#d46f15';
    }
    else if ($figaroBody.hasClass('admin-color-midnight')) {
        brandColor = '#69a8bb';
    }
    else if ($figaroBody.hasClass('admin-color-ocean')) {
        brandColor = '#aa9d88';
    }
    else if ($figaroBody.hasClass('admin-color-sunrise')) {
        brandColor = '#ccaf0b';
    }

    // attachAllDynamicSelects();
    attachAllDynamicPostTypes();

    jQuery('.fme-toggle-option').each(function () {
        var $thisElement = jQuery(this),
            elementType = $thisElement.attr('type'),
            toggleItems = $thisElement.data('fme-toggle');

        toggleItems = jQuery(toggleItems).hide();

        if (elementType = 'checkbox') {
            if ($thisElement.is(':checked')) {
                toggleItems.slideDown();
            };

            $thisElement.change(function () {
                toggleItems.slideToggle('fast');

                // CodeMirror
                toggleItems.find('.CodeMirror').each(function (i, el) {
                    el.CodeMirror.refresh();
                });

            });
        }
    });

    /* Reset button message
    ------------------------------------------------------------------------------------------ */
    jQuery('#fme-reset-settings').click(function () {
        var message = jQuery(this).data('message'),
            reset = confirm(message);

        if (!reset) {
            return false;
        }
    });

    /* WIDGETS | CUSTOM SIDEBAR SECTIONS
    ------------------------------------------------------------------------------------------ */
    if (jQuery('.widgets-php div[id*="figaropost-"]').length) {
        jQuery('#fme-show-sections-sidebars-wrap').show();

        jQuery('.widgets-php div[id*="figaropost-"]').parent().addClass('fme-sidebar-section');
        jQuery('#fme-show-sections-sidebars').change(function () {
            jQuery('.fme-sidebar-section, .fme-chooser-section').toggle();
        });
    }

    /* OPTIONS SEARCH
    ------------------------------------------------------------------------------------------ */
    $searchSettings = jQuery('#fme-panel-search'),
        $searchList = jQuery('#fme-search-list');

    $searchSettings.on('keyup', function () {
        var valThis = $searchSettings.val().toLowerCase();
        $searchList.html('');

        if (valThis == '') {
            jQuery('.highlights-search').removeClass('highlights-search');
        }

        else {
            jQuery('.fme-label').each(function () {
                var $thisElem = jQuery(this),
                    thistext = $thisElem.text();

                if (thistext.toLowerCase().indexOf(valThis) >= 0) {
                    $thisElem.addClass('highlights-search');

                    var thistextid = $thisElem.closest('.option-item').attr('id'),
                        $thisparent = jQuery(this).closest('.tabs-wrap'),
                        thistextparent = $thisparent.find('.fme-tab-head h2').text(),
                        thistextparentid = $thisparent.attr('id');

                    $searchList.append('<li><a href="#" data-section="' + thistextid + '" data-url="' + thistextparentid + '"><strong>' + thistextparent + '</strong> / ' + thistext + '</a></li>');
                }
                else {
                    $thisElem.removeClass('highlights-search');
                }
            });
        };
    });

    $searchList.on('click', 'a', function () {
        var $thisElem = jQuery(this),
            tabId = $thisElem.data('url'),
            tabsection = $thisElem.data('section');

        jQuery('.fme-panel-tabs ul li').removeClass('active');
        jQuery('.fme-panel-tabs').find('.' + tabId).addClass('active');
        jQuery('.tabs-wrap').hide();
        jQuery('#' + tabId).show();
        jQuery('html,body').unbind().animate({ scrollTop: jQuery('#' + tabsection).offset().top - 50 }, 'slow');
        return false;
    });


    $doc.mouseup(function (e) {
        var container = jQuery('#fme-options-search-wrap');

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $searchList.html('');
            $searchSettings.val('');
            jQuery('.highlights-search').removeClass('highlights-search');
        }
    });


        /* Stikcy Bottom Save Button */
    var lastScrollTop = 0,
            $topSaveButton = jQuery('.fme-panel-content'),
            $bottomSaveButton = jQuery('.fme-footer .fme-save-button');

    stickySaveButton = function () {
        var topSaveOffset = $topSaveButton.offset().top,
            scrollTop = $window.scrollTop(),
            scrollBottom = $doc.height() - scrollTop - $window.height(),
            st = scrollTop;

        if (scrollTop > topSaveOffset && scrollBottom > 105 - $bottomSaveButton.height()) {
            if (st > lastScrollTop) {
                $bottomSaveButton.addClass('sticky-on-down').removeClass('sticky-on-up');

                if (scrollTop > topSaveOffset) {
                    $bottomSaveButton.addClass('sticky-on-down-appear').removeClass('sticky-on-up-disappear');
                }
            }
            else {
                $bottomSaveButton.addClass('sticky-on-up').removeClass('sticky-on-down');

                if (scrollTop < topSaveOffset) {
                    $bottomSaveButton.addClass('sticky-on-up-disappear').removeClass('sticky-on-up-appear');
                }
            }
        }
        else {
            $bottomSaveButton.removeClass('sticky-on-down sticky-on-up sticky-on-down-appear sticky-on-up-disappear');
        }

        lastScrollTop = st;
    }


    stickySaveButton();

    $window.scroll(function () {
        stickySaveButton();
    });

    /* PAGE BUILDER
    ------------------------------------------------------------------------------------------ */
    /* Assign a Class to the builder depending on the Image Style */
    $doc.on('click', '#fme-builder-wrapper .fme-options a', function () {
        var $thisBlock = jQuery(this),
            $thisImg = $thisBlock.find('img'),
            $thisParent = $thisBlock.closest('.block-item'),
            blockClass = $thisImg.attr('class') + '-container';
        imgPath = $thisImg.attr('src'),
            postsNumber = $thisImg.data('number');

        if (postsNumber) {
            $thisParent.find('.block-number-item-options input').val(postsNumber);
        }

        $thisParent.attr('class', 'block-item parent-item ' + blockClass);
        $thisParent.find('.block-small-img').attr('src', imgPath);
    });

    /* Blocks Color Picker */
    var figaroBlocksColorsOptions = {
        change: function (event, ui) {
            var newColor = ui.color.toString();
            jQuery(this).closest('.block-item').find('.fme-block-head').attr('style', 'background-color: ' + newColor).removeClass('block-head-light block-head-dark').addClass('block-head-' + getContrastColor(newColor));
        },
        clear: function () {
            jQuery(this).closest('.block-item').find('.fme-block-head').attr('style', '').removeClass('block-head-light block-head-dark');
        }
    };

    if (jQuery().wpColorPicker) {
        jQuery('.figaroBlocksColor').wpColorPicker(figaroBlocksColorsOptions);
    }

    /* Blocks Color Picker */
    var figaroTitleColorsOptions = {
        change: function (event, ui) {
            var newColor = ui.color.toString();
            jQuery(this).closest('.block-item').find('.fme-block-head .block-preview-title').attr('style', 'color: ' + newColor).removeClass('block-head-light block-head-dark').addClass('block-head-' + getContrastColor(newColor));
        },
        clear: function () {
            jQuery(this).closest('.block-item').find('.fme-block-head .block-preview-title').attr('style', '').removeClass('block-head-light block-head-dark');
        }
    };

    if (jQuery().wpColorPicker) {
        jQuery('.figaroTitleColor').wpColorPicker(figaroTitleColorsOptions);
    }

    /* Toggle open/Close */
    $doc.on('click', '.toggle-section', function () {
        var $thisElement = jQuery(this).closest('.fme-builder-container');
        $thisElement.find('.fme-builder-section-inner').slideToggle('fast');
        $thisElement.toggleClass('fme-section-open');
        return false;
    });

    /* Chnage the Block Title */
    $doc.on('keyup', '.block-title-item-options input', function () {
        var NewTitleText = jQuery(this).val();
        jQuery(this).parents('.block-item').find('.block-preview-title').text(NewTitleText);
    });

    /* Edit Block/Section */
    $doc.on('click', '.edit-block-icon', function () {
        var $thisElement = jQuery(this).closest('.parent-item');
        $figaroBody.addClass('has-overlay');
        $thisElement.find('> .fme-builder-content-area').fadeIn('fast');

        // Disable the Sortable and Draggable if the PopUp is open
        jQuery('#fme-builder-wrapper, .fme-builder-blocks-wrapper').sortable('disable');
        jQuery('.block-item').draggable('disable');
        // ----

        return false;
    });

    /* Block Settings */
    $doc.on('click', '.blocks-settings-tabs a', function () {
        var $thisButtonTab = jQuery(this),
            $blockContent = $thisButtonTab.closest('.fme-builder-content-area'),
            targetTab = $thisButtonTab.data('target');

        $thisButtonTab.parent().find('a').removeClass('active');
        $thisButtonTab.addClass('active');

        $blockContent.find('.block-settings').hide();
        $blockContent.find('.' + targetTab).show();

        return false;
    });

    /* Assign a Class to the slider depending on the Image Style */
    $doc.on('click', '#fme_featured_posts_style a', function () {
        var sliderClass = jQuery(this).find('img').attr('class') + '-container';
        jQuery('#main-slider-options').attr('class', sliderClass);
        return false;
    });

    /* Categories Tabs box */
    jQuery('.tabs_cats input:checked').parent().addClass('selected');
    $doc.on('click', '.tabs_cats span', function (event) {
        var $thisTab = jQuery(this).parent();

        if ($thisTab.find(':checkbox').is(':checked')) {
            event.preventDefault();
            $thisTab.removeClass('selected');
            $thisTab.find(':checkbox').removeAttr('checked');
        } else {
            $thisTab.addClass('selected');
            $thisTab.find(':checkbox').attr('checked', 'checked');
        }
    });



    /* Misc
    ------------------------------------------------------------------------------------------ */
    /* COLOR PICKER */
    if (jQuery().wpColorPicker) {
        fme_color_picker();
    }


    /* PAGE BUILDER DRAG AND DROP */
    fme_builder_dragdrop();


    /* IMAGE UPLOADER PREVIEW */
    jQuery('.fme-img-path').each(function () {
        fme_image_uploader_trigger(jQuery(this));
    });


    /* Font Uploader */
    jQuery('.fme-font-path').each(function () {
        fme_set_font_uploader(jQuery(this));
    });


    /* CHECKBOXES */
    var checkInputs = Array.prototype.slice.call(document.querySelectorAll('.fme-js-switch'));
    checkInputs.forEach(function (html) {
        new Switchery(html, { color: brandColor });
    });


    /* MAIN MENU UPDATES NOTIFICATION */
    if (jQuery('li.menu-top.toplevel_page_fme-plugin-options .fme-plugin-update').length) {
        jQuery('li.menu-top.toplevel_page_fme-plugin-options .wp-menu-name').append(' <span class="update-plugins"><span class="update-count">' + tieLang.update + '</span></span>');
    }

    /* Widgets
        ------------------------------------------------------------------------------------------ */
    if ($figaroBody.hasClass('widgets-php') || $figaroBody.hasClass('nav-menus-php') || $figaroBody.hasClass('post-type-page')) {
        $doc.ajaxComplete(function () {
            jQuery('.figaroColorSelector').wpColorPicker();
        });
    }

    /* Widget Tabs Sortable  */
    jQuery('.tab-sortable').each(function () {
        fme_sortable_tabs_trigger(jQuery(this));
    });

    /* Widget Posts order option  */
    jQuery('.fme-posts-order-option').each(function () {
        fme_widget_posts_order(jQuery(this));
    });

    /* Trigger when Widget Added */
    $doc.on('widget-added', function (event, widgetContainer) {

        var $thisTabs = widgetContainer.find('.tab-sortable');
        fme_sortable_tabs_trigger($thisTabs);

        // ------
        var $thisOption = widgetContainer.find('.fme-posts-order-option');
        fme_widget_posts_order($thisOption);
    });

    /* Trigger when Widget Updated */
    $doc.on('widget-updated', function (event, widgetContainer) {

        var $thisTabs = widgetContainer.find('.tab-sortable');
        fme_sortable_tabs_trigger($thisTabs);

        // ------
        var $thisOption = widgetContainer.find('.fme-posts-order-option');
        fme_widget_posts_order($thisOption);
    });

    /* DISMISS NOTICES
    ------------------------------------------------------------------------------------------ */
    $doc.on('click', '.fme-notice .notice-dismiss', function () {

        jQuery('#fme-page-overlay').hide();

        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            data: {
                pointer: jQuery(this).closest('.fme-notice').attr('id'),
                action: 'dismiss-wp-pointer',
            },
        });
    });

    /* SAVE PLUGIN SETTINGS
    ------------------------------------------------------------------------------------------ */
    var $saveAlert = jQuery('#fme-saving-settings');

    jQuery('#fme_form').submit(function () {

        // Check if the import field has a file
        var importSettings = jQuery('#fme-import-file').val();
        if (importSettings.length > 0) {
            return true;
        }

        // Disable all blank fields to reduce the size of the data
        jQuery('form#fme_form input, form#fme_form textarea, form#fme_form select').each(function () {
            if (!jQuery(this).val()) {
                jQuery(this).attr('disabled', true);
            }
        });

        // Serialize the data array
        var data = jQuery(this).serialize();

        // Re-activate the disabled options
        jQuery('form#fme_form input:disabled, form#fme_form textarea:disabled, form#fme_form select:disabled').attr('disabled', false);

        // Add the Overlay layer and reset the saving spinner
        $figaroBody.addClass('has-overlay');
        $saveAlert.removeClass('is-success is-failed');

        // Send the Saving Ajax request
        jQuery.post(
            ajaxurl,
            data,
            function (response) {
                console.log(response);
                if (response.data == 1) {
                    $saveAlert.addClass('is-success').delay(900).fadeOut(700);
                    setTimeout(function () { $figaroBody.removeClass('has-overlay'); }, 1200);
                }
                else if (response.data == 2) {
                    location.reload();
                }
                else {
                    $saveAlert.addClass('is-failed').delay(900).fadeOut(700);
                    setTimeout(function () { $figaroBody.removeClass('has-overlay'); }, 1200);
                }
            });

        return false;
    });


    /* SAVE SETTINGS ALERT */
    $saveAlert.fadeOut();
    jQuery('.fme-save-button').click(function () {
        $saveAlert.fadeIn();
    });

    /* SETTINGS PANEL
    ------------------------------------------------------------------------------------------ */
    jQuery('.fme-panel, .fme-notice').css({ 'opacity': 1, 'visibility': 'visible' });

    var tabsHeight = jQuery('.fme-panel-tabs').outerHeight();
    jQuery('.tabs-wrap').hide();
    jQuery('.fme-panel-tabs ul li:first').addClass('active').show();
    jQuery('.tabs-wrap:first').show();
    jQuery('.fme-panel-content').css({ minHeight: tabsHeight });

    jQuery('li.fme-tabs:not(.fme-not-tab)').click(function () {
        jQuery('.fme-panel-tabs ul li').removeClass('active');
        jQuery(this).addClass('active');
        jQuery('.tabs-wrap').hide();
        var activeTab = jQuery(this).find('a').attr('href');
        jQuery(activeTab).show();
        document.location.hash = activeTab + '-target';

        // CodeMirror
        jQuery(activeTab).find('.CodeMirror').each(function (i, el) {
            el.CodeMirror.refresh();
        });

    });

    /* GO TO THE OPENED TAB WITH LOAD */
    var currentTab = window.location.hash.replace('-target', '');
    currentTab = currentTab.replace(/\//g, ''); // avoid issues when the URL contains something like #/campaign/0/contacts

    if (jQuery(currentTab).parent('#fme_form').length) {
        var tabLinkClass = currentTab.replace('#', '.');
        jQuery('.tabs-wrap').hide();
        jQuery('.fme-panel-tabs ul li').removeClass('active');
        jQuery(currentTab).show();
        jQuery(tabLinkClass).addClass('active');
    }

    /* DELETE SECTIONS
    ------------------------------------------------------------------------------------------ */
    /* OPTION ITEM */
    $doc.on('click', '.del-item', function () {
        var $thisButton = jQuery(this);

        if ($thisButton.hasClass('del-custom-sidebar')) {
            var option = $thisButton.parent().find('input').val();
            jQuery('#custom-sidebars select').find('option[value="' + option + '"]').remove();
        }

        if ($thisButton.hasClass('del-section')) {
            var widgets = $thisButton.closest('.parent-item').find('.fme-manage-widgets').data('widgets');
            jQuery('#wrap-' + widgets + ', #' + widgets + '-sidebar-options').remove();
        }

        $thisButton.closest('.parent-item').addClass('removed').fadeOut(function () {
            $thisButton.closest('.parent-item').remove();
        });

        return false;
    });

    /* DELETE PREVIEW IMAGE */
    $doc.on('click', '.del-img', function () {
        var $img = jQuery(this).parent();
        $img.fadeOut('fast', function () {
            $img.hide();
            $img.closest('.option-item').find('.fme-img-path').attr('value', '');
        });
    });

    /* DELETE PREVIEW IMAGE */
    $doc.on('click', '.del-img-all', function () {
        var $imgLi = jQuery(this).closest('li');
        $imgLi.fadeOut('fast', function () {
            $imgLi.remove();
        });
    });

    /* ADD HIGHLIGHTS
    ------------------------------------------------------------------------------------------ */
    jQuery('#add_highlights_button').click(function () {
        var customtext = tieHTMLspecialchars(jQuery('#custom_text').val());
        if (customtext.length > 0) {
            jQuery('#highlights_custom_error-item').slideUp();
            jQuery('#customList').append('\
				<li class="parent-item">\
					<div class="fme-block-head">\
						'+ customtext + '\
						<input name="_fme_hlights_text['+ customnext + ']" type="hidden" value="' + customtext + '" />\
						<a class="del-item dashicons dashicons-trash"></a>\
					</div>\
				</li>\
			');
        }
        else {
            jQuery('#highlights_custom_error-item').fadeIn();
        }

        customnext++;
        jQuery('#custom_text').val('');
    });

    /* ADD Sources
    ------------------------------------------------------------------------------------------ */
    jQuery('#add_source_button').click(function () {
        var source_name = tieHTMLspecialchars(jQuery('#source_name').val()),
            source_link = jQuery('#source_link').val();

        if (source_name.length > 0) {
            jQuery('#add-source-error-item').slideUp();

            var source_code = '\
				<li class="parent-item">\
					<div class="fme-block-head">';

            if (source_link.length > 0) {
                source_code += '\
							<a href="'+ source_link + '" target="_blank">' + source_name + '</a>\
							<input name="_fme_post_source['+ source_next + '][url]" type="hidden" value="' + source_link + '" />\
						';
            }
            else {
                source_code += source_name;
            }

            source_code += '\
						<input name="_fme_post_source['+ source_next + '][text]" type="hidden" value="' + source_name + '" />\
						<a class="del-item dashicons dashicons-trash"></a>\
					</div>\
				</li>\
			';

            jQuery('#sources-list').append(source_code);
        }
        else {
            jQuery('#add-source-error-item').fadeIn();
        }

        source_next++;
        jQuery('#source_link, #source_name').val('');
    });

    /* ADD Via
    ------------------------------------------------------------------------------------------ */
    jQuery('#add_via_button').click(function () {
        var via_name = tieHTMLspecialchars(jQuery('#via_name').val()),
            via_link = jQuery('#via_link').val();

        if (via_name.length > 0) {

            jQuery('#add-via-error-item').slideUp();

            var via_code = '\
				<li class="parent-item">\
					<div class="fme-block-head">';

            if (via_link.length > 0) {
                via_code += '\
							<a href="'+ via_link + '" target="_blank">' + via_name + '</a>\
							<input name="_fme_post_via['+ via_next + '][url]" type="hidden" value="' + via_link + '" />\
						';
            }
            else {
                via_code += via_name;
            }

            via_code += '\
						<input name="_fme_post_via['+ via_next + '][text]" type="hidden" value="' + via_name + '" />\
						<a class="del-item dashicons dashicons-trash"></a>\
					</div>\
				</li>\
			';

            jQuery('#via-list').append(via_code);
        }
        else {
            jQuery('#add-via-error-item').fadeIn();
        }

        via_next++;
        jQuery('#via_link, #via_name').val('');
    });

    /* VISUAL OPTIONS
    ------------------------------------------------------------------------------------------ */
    jQuery('ul.fme-options').each(function (index) {
        jQuery(this).find('input:checked').parent().addClass('selected');
    });

    $doc.on('click', 'ul.fme-options a', function () {
        var $thisBlock = jQuery(this),
            blockID = $thisBlock.closest('ul.fme-options').attr('id');

        jQuery('#' + blockID).find('li').removeClass('selected');
        jQuery('#' + blockID).find(':radio').removeAttr('checked');
        $thisBlock.parent().find(':radio').trigger('click');
        $thisBlock.parent().addClass('selected');
        $thisBlock.parent().find(':radio').attr('checked', 'checked');
        return false;
    });

    /* SLIDERS - Category Options
    ------------------------------------------------------------------------------------------ */
    // Show/hide slider and video playlist options

    if ($figaroBody.hasClass('taxonomy-category')) {

        var $featured_posts_options = jQuery('.featured-posts-options').hide(),
            $featured_videos_options = jQuery('.featured-videos-options').hide();

        selected_val = jQuery('.visual-option-videos_list').find('input:checked').val();

        if (selected_val == 'videos_list') {
            $featured_videos_options.show();
        } else {
            $featured_posts_options.show();
        }

        $doc.on('click', '#fme_featured_posts_style a', function () {
            var selected_val = jQuery(this).closest('li').find('input').val();

            if (selected_val == 'videos_list') {
                $featured_posts_options.hide();
                $featured_videos_options.show();
            } else {
                $featured_videos_options.hide();
                $featured_posts_options.show();
            }
        });
    }



    /* PREDEFINED SKINS
    ------------------------------------------------------------------------------------------ */
    jQuery('.predefined-skins-options select').change(function () {
        var skin = jQuery(this).val(),
            skin_colors = fme_skins[skin];

        jQuery('#fme-options-tab-styling').find('.figaroColorSelector').val('');
        jQuery('#fme-options-tab-styling').find('.wp-color-result').attr('style', '');

        for (var key in skin_colors) {
            if (skin_colors.hasOwnProperty(key)) {
                jQuery('#' + key).wpColorPicker('color', skin_colors[key]);
            }
        }
    });

});



/* Fire Sortable on the Widgets Tabs
------------------------------------------------------------------------------------------ */
function fme_sortable_tabs_trigger($thisTabs) {

    $thisTabs.sortable({
        placeholder: 'fme-state-highlight',

        stop: function (event, ui) {
            var data = '';

            $thisTabs.find('li').each(function () {
                var type = jQuery(this).data('tab');
                data += type + ',';
            });

            $thisTabs.parent().find('.stored-tabs-order').val(data.slice(0, -1));
        }
    });
}

/* IMAGE UPLOADER PREVIEW
------------------------------------------------------------------------------------------ */
function fme_image_uploader_trigger($thisElement) {

    var thisElementID = $thisElement.attr('id').replace('#', ''),
        $thisElementParent = $thisElement.closest('.option-item'),
        $thisElementImage = $thisElementParent.find('.img-preview'),
        uploaderTypeStyles = false;

    $thisElement.change(function () {
        $thisElementImage.show();
        $thisElementImage.find('img').attr('src', $thisElement.val());
    });

    if ($thisElement.hasClass('fme-background-path')) {
        thisElementID = thisElementID.replace('-img', '');
        uploaderTypeStyles = true;
    }

    fme_set_uploader(thisElementID, uploaderTypeStyles);
}



/* IMAGE UPLOADER FUNCTIONS
------------------------------------------------------------------------------------------ */
function fme_builder_dragdrop() {

    jQuery('#fme-builder-wrapper').sortable({
        placeholder: 'fme-state-highlight fme-state-sections',
        activate: function (event, ui) {

            var $sortableWrap = ui.item,
                outerHeight = $sortableWrap.outerHeight() + 40;

            jQuery('.fme-state-sections').css('min-height', outerHeight);
        },
    });

    jQuery('.tabs_cats').sortable({ placeholder: 'fme-state-highlight' });

    jQuery('.block-item').draggable({
        distance: 2,
        refreshPositions: true,
        containment: '#wpwrap',
        cursor: 'move',
        zIndex: 100,
        connectToSortable: '.fme-builder-blocks-wrapper',
        revert: true,
        revertDuration: 0,

        /*start: function( event, ui ) {
            ui.helper.css('width', ui.helper.width());
        },*/

        stop: function (event, ui) {
            ui.helper.css('width', '100%');
        }
    });

    jQuery('.fme-builder-blocks-wrapper').sortable({
        placeholder: 'fme-state-highlight',
        items: '> .block-item',
        cursor: 'move',
        distance: 2,
        containment: '#wpwrap',
        tolerance: 'pointer',
        refreshPositions: true,

        receive: function (event, ui) {
            var sectionID = jQuery(this).data('section-id');

            ui.item.find('[name^=fme_home_cats]').each(function () {
                var newName = jQuery(this).attr('name').replace(/fme_home_cats\[(\w+)\]/g, 'fme_home_cats\[' + sectionID + ']');
                jQuery(this).attr('name', newName);
            });
        },

        activate: function (event, ui) {
            jQuery('.fme-builder-blocks-wrapper').css('min-height', '65px');
            var $sortableWrap = ui.item.closest('.fme-builder-blocks-wrapper'),
                outerHeight = ($sortableWrap.outerHeight() > 0) ? $sortableWrap.outerHeight() + 40 : '65px';

            $sortableWrap.css('min-height', outerHeight);
            jQuery('.fme-builder-container').addClass('fme-block-hover');
        },

        deactivate: function () {
            jQuery('.fme-builder-container').removeClass('fme-block-hover');
            jQuery('.fme-builder-blocks-wrapper').css('min-height', '');
        },
    }).sortable('option', 'connectWith', '.fme-builder-container');

}


/* IMAGE UPLOADER FUNCTIONS
------------------------------------------------------------------------------------------ */
function fme_set_uploader(field, styling) {
    var fme_bg_uploader;

    $doc.on('click', '#upload_' + field + '_button', function (event) {

        event.preventDefault();
        fme_bg_uploader = wp.media.frames.fme_bg_uploader = wp.media({
            title: 'Choose Image',
            library: { type: 'image' },
            button: { text: 'Select' },
            multiple: false
        });

        fme_bg_uploader.on('select', function () {
            var selection = fme_bg_uploader.state().get('selection');
            selection.map(function (attachment) {

                attachment = attachment.toJSON();

                if (styling) {
                    jQuery('#' + field + '-img').val(attachment.url);
                }

                else {
                    jQuery('#' + field).val(attachment.url);
                }

                jQuery('#' + field + '-preview').show();
                jQuery('#' + field + '-preview img').attr('src', attachment.url);
            });
        });

        fme_bg_uploader.open();
    });
}



/* Custom Color Picker
------------------------------------------------------------------------------------------ */
function fme_color_picker() {
    Color.prototype.toString = function (remove_alpha) {
        if (remove_alpha == 'no-alpha') {
            return this.toCSS('rgba', '1').replace(/\s+/g, '');
        }
        if (this._alpha < 1) {
            return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
        }
        var hex = parseInt(this._color, 10).toString(16);
        if (this.error) return '';
        if (hex.length < 6) {
            for (var i = 6 - hex.length - 1; i >= 0; i--) {
                hex = '0' + hex;
            }
        }
        return '#' + hex;
    };

    jQuery('.figaroColorSelector').each(function () {

        var $control = jQuery(this),
            value = $control.val().replace(/\s+/g, ''),
            palette_input = $control.attr('data-palette');

        if (palette_input == 'false' || palette_input == false) {
            var palette = false;
        }
        else if (palette_input == 'true' || palette_input == true) {
            var palette = true;
        }
        else {
            var palette = $control.attr('data-palette').split(",");
        }

        $control.wpColorPicker({ // change some things with the color picker
            clear: function (event, ui) {
                // TODO reset Alpha Slider to 100
            },
            change: function (event, ui) {
                var $transparency = $control.parents('.wp-picker-container:first').find('.transparency');
                $transparency.css('backgroundColor', ui.color.toString('no-alpha'));
            },
            palettes: palette
        });

        jQuery('<div class="fme-alpha-container"><div class="slider-alpha"></div><div class="transparency"></div></div>').appendTo($control.parents('.wp-picker-container'));
        var $alpha_slider = $control.parents('.wp-picker-container:first').find('.slider-alpha');
        if (value.match(/rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/)) {
            var alpha_val = parseFloat(value.match(/rgba\(\d+\,\d+\,\d+\,([^\)]+)\)/)[1]) * 100;
            var alpha_val = parseInt(alpha_val);
        }
        else {
            var alpha_val = 100;
        }

        $alpha_slider.slider({
            slide: function (event, ui) {
                jQuery(this).find('.ui-slider-handle').text(ui.value); // show value on slider handle
            },
            create: function (event, ui) {
                var v = jQuery(this).slider('value');
                jQuery(this).find('.ui-slider-handle').text(v);
            },
            value: alpha_val,
            range: 'max',
            step: 1,
            min: 1,
            max: 100
        });

        $alpha_slider.slider().on('slidechange', function (event, ui) {
            var new_alpha_val = parseFloat(ui.value),
                iris = $control.data('a8cIris'),
                color_picker = $control.data('wpWpColorPicker');

            iris._color._alpha = new_alpha_val / 100.0;

            $control.val(iris._color.toString());
            color_picker.toggler.css({
                backgroundColor: $control.val()
            });

            var get_val = $control.val();
            jQuery($control).wpColorPicker('color', get_val);
        });
    });
}


/* htmlspecialchars in JS */
function tieHTMLspecialchars(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}


/* Switcher: IOS Style Switch Button | http://abpetkov.github.io/switchery */
(function () { function require(name) { var module = require.modules[name]; if (!module) throw new Error('failed to require "' + name + '"'); if (!("exports" in module) && typeof module.definition === "function") { module.client = module.component = true; module.definition.call(this, module.exports = {}, module); delete module.definition } return module.exports } require.loader = "component"; require.helper = {}; require.helper.semVerSort = function (a, b) { var aArray = a.version.split("."); var bArray = b.version.split("."); for (var i = 0; i < aArray.length; ++i) { var aInt = parseInt(aArray[i], 10); var bInt = parseInt(bArray[i], 10); if (aInt === bInt) { var aLex = aArray[i].substr(("" + aInt).length); var bLex = bArray[i].substr(("" + bInt).length); if (aLex === "" && bLex !== "") return 1; if (aLex !== "" && bLex === "") return -1; if (aLex !== "" && bLex !== "") return aLex > bLex ? 1 : -1; continue } else if (aInt > bInt) { return 1 } else { return -1 } } return 0 }; require.latest = function (name, returnPath) { function showError(name) { throw new Error('failed to find latest module of "' + name + '"') } var versionRegexp = /(.*)~(.*)@v?(\d+\.\d+\.\d+[^\/]*)$/; var remoteRegexp = /(.*)~(.*)/; if (!remoteRegexp.test(name)) showError(name); var moduleNames = Object.keys(require.modules); var semVerCandidates = []; var otherCandidates = []; for (var i = 0; i < moduleNames.length; i++) { var moduleName = moduleNames[i]; if (new RegExp(name + "@").test(moduleName)) { var version = moduleName.substr(name.length + 1); var semVerMatch = versionRegexp.exec(moduleName); if (semVerMatch != null) { semVerCandidates.push({ version: version, name: moduleName }) } else { otherCandidates.push({ version: version, name: moduleName }) } } } if (semVerCandidates.concat(otherCandidates).length === 0) { showError(name) } if (semVerCandidates.length > 0) { var module = semVerCandidates.sort(require.helper.semVerSort).pop().name; if (returnPath === true) { return module } return require(module) } var module = otherCandidates.pop().name; if (returnPath === true) { return module } return require(module) }; require.modules = {}; require.register = function (name, definition) { require.modules[name] = { definition: definition } }; require.define = function (name, exports) { require.modules[name] = { exports: exports } }; require.register("abpetkov~transitionize@0.0.3", function (exports, module) { module.exports = Transitionize; function Transitionize(element, props) { if (!(this instanceof Transitionize)) return new Transitionize(element, props); this.element = element; this.props = props || {}; this.init() } Transitionize.prototype.isSafari = function () { return /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor) }; Transitionize.prototype.init = function () { var transitions = []; for (var key in this.props) { transitions.push(key + " " + this.props[key]) } this.element.style.transition = transitions.join(", "); if (this.isSafari()) this.element.style.webkitTransition = transitions.join(", ") } }); require.register("ftlabs~fastclick@v0.6.11", function (exports, module) { function FastClick(layer) { "use strict"; var oldOnClick, self = this; this.trackingClick = false; this.trackingClickStart = 0; this.targetElement = null; this.touchStartX = 0; this.touchStartY = 0; this.lastTouchIdentifier = 0; this.touchBoundary = 10; this.layer = layer; if (!layer || !layer.nodeType) { throw new TypeError("Layer must be a document node") } this.onClick = function () { return FastClick.prototype.onClick.apply(self, arguments) }; this.onMouse = function () { return FastClick.prototype.onMouse.apply(self, arguments) }; this.onTouchStart = function () { return FastClick.prototype.onTouchStart.apply(self, arguments) }; this.onTouchMove = function () { return FastClick.prototype.onTouchMove.apply(self, arguments) }; this.onTouchEnd = function () { return FastClick.prototype.onTouchEnd.apply(self, arguments) }; this.onTouchCancel = function () { return FastClick.prototype.onTouchCancel.apply(self, arguments) }; if (FastClick.notNeeded(layer)) { return } if (this.deviceIsAndroid) { layer.addEventListener("mouseover", this.onMouse, true); layer.addEventListener("mousedown", this.onMouse, true); layer.addEventListener("mouseup", this.onMouse, true) } layer.addEventListener("click", this.onClick, true); layer.addEventListener("touchstart", this.onTouchStart, false); layer.addEventListener("touchmove", this.onTouchMove, false); layer.addEventListener("touchend", this.onTouchEnd, false); layer.addEventListener("touchcancel", this.onTouchCancel, false); if (!Event.prototype.stopImmediatePropagation) { layer.removeEventListener = function (type, callback, capture) { var rmv = Node.prototype.removeEventListener; if (type === "click") { rmv.call(layer, type, callback.hijacked || callback, capture) } else { rmv.call(layer, type, callback, capture) } }; layer.addEventListener = function (type, callback, capture) { var adv = Node.prototype.addEventListener; if (type === "click") { adv.call(layer, type, callback.hijacked || (callback.hijacked = function (event) { if (!event.propagationStopped) { callback(event) } }), capture) } else { adv.call(layer, type, callback, capture) } } } if (typeof layer.onclick === "function") { oldOnClick = layer.onclick; layer.addEventListener("click", function (event) { oldOnClick(event) }, false); layer.onclick = null } } FastClick.prototype.deviceIsAndroid = navigator.userAgent.indexOf("Android") > 0; FastClick.prototype.deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent); FastClick.prototype.deviceIsIOS4 = FastClick.prototype.deviceIsIOS && /OS 4_\d(_\d)?/.test(navigator.userAgent); FastClick.prototype.deviceIsIOSWithBadTarget = FastClick.prototype.deviceIsIOS && /OS ([6-9]|\d{2})_\d/.test(navigator.userAgent); FastClick.prototype.needsClick = function (target) { "use strict"; switch (target.nodeName.toLowerCase()) { case "button": case "select": case "textarea": if (target.disabled) { return true } break; case "input": if (this.deviceIsIOS && target.type === "file" || target.disabled) { return true } break; case "label": case "video": return true }return /\bneedsclick\b/.test(target.className) }; FastClick.prototype.needsFocus = function (target) { "use strict"; switch (target.nodeName.toLowerCase()) { case "textarea": return true; case "select": return !this.deviceIsAndroid; case "input": switch (target.type) { case "button": case "checkbox": case "file": case "image": case "radio": case "submit": return false }return !target.disabled && !target.readOnly; default: return /\bneedsfocus\b/.test(target.className) } }; FastClick.prototype.sendClick = function (targetElement, event) { "use strict"; var clickEvent, touch; if (document.activeElement && document.activeElement !== targetElement) { document.activeElement.blur() } touch = event.changedTouches[0]; clickEvent = document.createEvent("MouseEvents"); clickEvent.initMouseEvent(this.determineEventType(targetElement), true, true, window, 1, touch.screenX, touch.screenY, touch.clientX, touch.clientY, false, false, false, false, 0, null); clickEvent.forwardedTouchEvent = true; targetElement.dispatchEvent(clickEvent) }; FastClick.prototype.determineEventType = function (targetElement) { "use strict"; if (this.deviceIsAndroid && targetElement.tagName.toLowerCase() === "select") { return "mousedown" } return "click" }; FastClick.prototype.focus = function (targetElement) { "use strict"; var length; if (this.deviceIsIOS && targetElement.setSelectionRange && targetElement.type.indexOf("date") !== 0 && targetElement.type !== "time") { length = targetElement.value.length; targetElement.setSelectionRange(length, length) } else { targetElement.focus() } }; FastClick.prototype.updateScrollParent = function (targetElement) { "use strict"; var scrollParent, parentElement; scrollParent = targetElement.fastClickScrollParent; if (!scrollParent || !scrollParent.contains(targetElement)) { parentElement = targetElement; do { if (parentElement.scrollHeight > parentElement.offsetHeight) { scrollParent = parentElement; targetElement.fastClickScrollParent = parentElement; break } parentElement = parentElement.parentElement } while (parentElement) } if (scrollParent) { scrollParent.fastClickLastScrollTop = scrollParent.scrollTop } }; FastClick.prototype.getTargetElementFromEventTarget = function (eventTarget) { "use strict"; if (eventTarget.nodeType === Node.TEXT_NODE) { return eventTarget.parentNode } return eventTarget }; FastClick.prototype.onTouchStart = function (event) { "use strict"; var targetElement, touch, selection; if (event.targetTouches.length > 1) { return true } targetElement = this.getTargetElementFromEventTarget(event.target); touch = event.targetTouches[0]; if (this.deviceIsIOS) { selection = window.getSelection(); if (selection.rangeCount && !selection.isCollapsed) { return true } if (!this.deviceIsIOS4) { if (touch.identifier === this.lastTouchIdentifier) { event.preventDefault(); return false } this.lastTouchIdentifier = touch.identifier; this.updateScrollParent(targetElement) } } this.trackingClick = true; this.trackingClickStart = event.timeStamp; this.targetElement = targetElement; this.touchStartX = touch.pageX; this.touchStartY = touch.pageY; if (event.timeStamp - this.lastClickTime < 200) { event.preventDefault() } return true }; FastClick.prototype.touchHasMoved = function (event) { "use strict"; var touch = event.changedTouches[0], boundary = this.touchBoundary; if (Math.abs(touch.pageX - this.touchStartX) > boundary || Math.abs(touch.pageY - this.touchStartY) > boundary) { return true } return false }; FastClick.prototype.onTouchMove = function (event) { "use strict"; if (!this.trackingClick) { return true } if (this.targetElement !== this.getTargetElementFromEventTarget(event.target) || this.touchHasMoved(event)) { this.trackingClick = false; this.targetElement = null } return true }; FastClick.prototype.findControl = function (labelElement) { "use strict"; if (labelElement.control !== undefined) { return labelElement.control } if (labelElement.htmlFor) { return document.getElementById(labelElement.htmlFor) } return labelElement.querySelector("button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea") }; FastClick.prototype.onTouchEnd = function (event) { "use strict"; var forElement, trackingClickStart, targetTagName, scrollParent, touch, targetElement = this.targetElement; if (!this.trackingClick) { return true } if (event.timeStamp - this.lastClickTime < 200) { this.cancelNextClick = true; return true } this.cancelNextClick = false; this.lastClickTime = event.timeStamp; trackingClickStart = this.trackingClickStart; this.trackingClick = false; this.trackingClickStart = 0; if (this.deviceIsIOSWithBadTarget) { touch = event.changedTouches[0]; targetElement = document.elementFromPoint(touch.pageX - window.pageXOffset, touch.pageY - window.pageYOffset) || targetElement; targetElement.fastClickScrollParent = this.targetElement.fastClickScrollParent } targetTagName = targetElement.tagName.toLowerCase(); if (targetTagName === "label") { forElement = this.findControl(targetElement); if (forElement) { this.focus(targetElement); if (this.deviceIsAndroid) { return false } targetElement = forElement } } else if (this.needsFocus(targetElement)) { if (event.timeStamp - trackingClickStart > 100 || this.deviceIsIOS && window.top !== window && targetTagName === "input") { this.targetElement = null; return false } this.focus(targetElement); if (!this.deviceIsIOS4 || targetTagName !== "select") { this.targetElement = null; event.preventDefault() } return false } if (this.deviceIsIOS && !this.deviceIsIOS4) { scrollParent = targetElement.fastClickScrollParent; if (scrollParent && scrollParent.fastClickLastScrollTop !== scrollParent.scrollTop) { return true } } if (!this.needsClick(targetElement)) { event.preventDefault(); this.sendClick(targetElement, event) } return false }; FastClick.prototype.onTouchCancel = function () { "use strict"; this.trackingClick = false; this.targetElement = null }; FastClick.prototype.onMouse = function (event) { "use strict"; if (!this.targetElement) { return true } if (event.forwardedTouchEvent) { return true } if (!event.cancelable) { return true } if (!this.needsClick(this.targetElement) || this.cancelNextClick) { if (event.stopImmediatePropagation) { event.stopImmediatePropagation() } else { event.propagationStopped = true } event.stopPropagation(); event.preventDefault(); return false } return true }; FastClick.prototype.onClick = function (event) { "use strict"; var permitted; if (this.trackingClick) { this.targetElement = null; this.trackingClick = false; return true } if (event.target.type === "submit" && event.detail === 0) { return true } permitted = this.onMouse(event); if (!permitted) { this.targetElement = null } return permitted }; FastClick.prototype.destroy = function () { "use strict"; var layer = this.layer; if (this.deviceIsAndroid) { layer.removeEventListener("mouseover", this.onMouse, true); layer.removeEventListener("mousedown", this.onMouse, true); layer.removeEventListener("mouseup", this.onMouse, true) } layer.removeEventListener("click", this.onClick, true); layer.removeEventListener("touchstart", this.onTouchStart, false); layer.removeEventListener("touchmove", this.onTouchMove, false); layer.removeEventListener("touchend", this.onTouchEnd, false); layer.removeEventListener("touchcancel", this.onTouchCancel, false) }; FastClick.notNeeded = function (layer) { "use strict"; var metaViewport; var chromeVersion; if (typeof window.ontouchstart === "undefined") { return true } chromeVersion = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [, 0])[1]; if (chromeVersion) { if (FastClick.prototype.deviceIsAndroid) { metaViewport = document.querySelector("meta[name=viewport]"); if (metaViewport) { if (metaViewport.content.indexOf("user-scalable=no") !== -1) { return true } if (chromeVersion > 31 && window.innerWidth <= window.screen.width) { return true } } } else { return true } } if (layer.style.msTouchAction === "none") { return true } return false }; FastClick.attach = function (layer) { "use strict"; return new FastClick(layer) }; if (typeof define !== "undefined" && define.amd) { define(function () { "use strict"; return FastClick }) } else if (typeof module !== "undefined" && module.exports) { module.exports = FastClick.attach; module.exports.FastClick = FastClick } else { window.FastClick = FastClick } }); require.register("component~indexof@0.0.3", function (exports, module) { module.exports = function (arr, obj) { if (arr.indexOf) return arr.indexOf(obj); for (var i = 0; i < arr.length; ++i) { if (arr[i] === obj) return i } return -1 } }); require.register("component~classes@1.2.1", function (exports, module) { var index = require("component~indexof@0.0.3"); var re = /\s+/; var toString = Object.prototype.toString; module.exports = function (el) { return new ClassList(el) }; function ClassList(el) { if (!el) throw new Error("A DOM element reference is required"); this.el = el; this.list = el.classList } ClassList.prototype.add = function (name) { if (this.list) { this.list.add(name); return this } var arr = this.array(); var i = index(arr, name); if (!~i) arr.push(name); this.el.className = arr.join(" "); return this }; ClassList.prototype.remove = function (name) { if ("[object RegExp]" == toString.call(name)) { return this.removeMatching(name) } if (this.list) { this.list.remove(name); return this } var arr = this.array(); var i = index(arr, name); if (~i) arr.splice(i, 1); this.el.className = arr.join(" "); return this }; ClassList.prototype.removeMatching = function (re) { var arr = this.array(); for (var i = 0; i < arr.length; i++) { if (re.test(arr[i])) { this.remove(arr[i]) } } return this }; ClassList.prototype.toggle = function (name, force) { if (this.list) { if ("undefined" !== typeof force) { if (force !== this.list.toggle(name, force)) { this.list.toggle(name) } } else { this.list.toggle(name) } return this } if ("undefined" !== typeof force) { if (!force) { this.remove(name) } else { this.add(name) } } else { if (this.has(name)) { this.remove(name) } else { this.add(name) } } return this }; ClassList.prototype.array = function () { var str = this.el.className.replace(/^\s+|\s+$/g, ""); var arr = str.split(re); if ("" === arr[0]) arr.shift(); return arr }; ClassList.prototype.has = ClassList.prototype.contains = function (name) { return this.list ? this.list.contains(name) : !!~index(this.array(), name) } }); require.register("switchery", function (exports, module) { var transitionize = require("abpetkov~transitionize@0.0.3"), fastclick = require("ftlabs~fastclick@v0.6.11"), classes = require("component~classes@1.2.1"); module.exports = Switchery; var defaults = { color: "#64bd63", secondaryColor: "#dfdfdf", jackColor: "#fff", className: "switchery", disabled: false, disabledOpacity: .5, speed: "0.4s", size: "default" }; function Switchery(element, options) { if (!(this instanceof Switchery)) return new Switchery(element, options); this.element = element; this.options = options || {}; for (var i in defaults) { if (this.options[i] == null) { this.options[i] = defaults[i] } } if (this.element != null && this.element.type == "checkbox") this.init() } Switchery.prototype.hide = function () { this.element.style.display = "none" }; Switchery.prototype.show = function () { var switcher = this.create(); this.insertAfter(this.element, switcher) }; Switchery.prototype.create = function () { this.switcher = document.createElement("span"); this.jack = document.createElement("small"); this.switcher.appendChild(this.jack); this.switcher.className = this.options.className; return this.switcher }; Switchery.prototype.insertAfter = function (reference, target) { reference.parentNode.insertBefore(target, reference.nextSibling) }; Switchery.prototype.isChecked = function () { return this.element.checked }; Switchery.prototype.isDisabled = function () { return this.options.disabled || this.element.disabled || this.element.readOnly }; Switchery.prototype.setPosition = function (clicked) { var checked = this.isChecked(), switcher = this.switcher, jack = this.jack; if (clicked && checked) checked = false; else if (clicked && !checked) checked = true; if (checked === true) { this.element.checked = true; if (window.getComputedStyle) jack.style.left = parseInt(window.getComputedStyle(switcher).width) - parseInt(window.getComputedStyle(jack).width) + "px"; else jack.style.left = parseInt(switcher.currentStyle["width"]) - parseInt(jack.currentStyle["width"]) + "px"; if (this.options.color) this.colorize(); this.setSpeed() } else { jack.style.left = 0; this.element.checked = false; this.switcher.style.boxShadow = "inset 0 0 0 0 " + this.options.secondaryColor; this.switcher.style.borderColor = this.options.secondaryColor; this.switcher.style.backgroundColor = this.options.secondaryColor !== defaults.secondaryColor ? this.options.secondaryColor : "#fff"; this.jack.style.backgroundColor = this.options.jackColor; this.setSpeed() } }; Switchery.prototype.setSpeed = function () { var switcherProp = {}, jackProp = { left: this.options.speed.replace(/[a-z]/, "") / 2 + "s" }; if (this.isChecked()) { switcherProp = { border: this.options.speed, "box-shadow": this.options.speed, "background-color": this.options.speed.replace(/[a-z]/, "") * 3 + "s" } } else { switcherProp = { border: this.options.speed, "box-shadow": this.options.speed } } transitionize(this.switcher, switcherProp); transitionize(this.jack, jackProp) }; Switchery.prototype.setSize = function () { var small = "switchery-small", normal = "switchery-default", large = "switchery-large"; switch (this.options.size) { case "small": classes(this.switcher).add(small); break; case "large": classes(this.switcher).add(large); break; default: classes(this.switcher).add(normal); break } }; Switchery.prototype.colorize = function () { var switcherHeight = this.switcher.offsetHeight / 2; this.switcher.style.backgroundColor = this.options.color; this.switcher.style.borderColor = this.options.color; this.switcher.style.boxShadow = "inset 0 0 0 " + switcherHeight + "px " + this.options.color; this.jack.style.backgroundColor = this.options.jackColor }; Switchery.prototype.handleOnchange = function (state) { if (document.dispatchEvent) { var event = document.createEvent("HTMLEvents"); event.initEvent("change", true, true); this.element.dispatchEvent(event) } else { this.element.fireEvent("onchange") } }; Switchery.prototype.handleChange = function () { var self = this, el = this.element; if (el.addEventListener) { el.addEventListener("change", function () { self.setPosition() }) } else { el.attachEvent("onchange", function () { self.setPosition() }) } }; Switchery.prototype.handleClick = function () { var self = this, switcher = this.switcher, parent = self.element.parentNode.tagName.toLowerCase(), labelParent = parent === "label" ? false : true; if (this.isDisabled() === false) { fastclick(switcher); if (switcher.addEventListener) { switcher.addEventListener("click", function (e) { self.setPosition(labelParent); self.handleOnchange(self.element.checked) }) } else { switcher.attachEvent("onclick", function () { self.setPosition(labelParent); self.handleOnchange(self.element.checked) }) } } else { this.element.disabled = true; this.switcher.style.opacity = this.options.disabledOpacity } }; Switchery.prototype.markAsSwitched = function () { this.element.setAttribute("data-switchery", true) }; Switchery.prototype.markedAsSwitched = function () { return this.element.getAttribute("data-switchery") }; Switchery.prototype.init = function () { this.hide(); this.show(); this.setSize(); this.setPosition(); this.markAsSwitched(); this.handleChange(); this.handleClick() } }); if (typeof exports == "object") { module.exports = require("switchery") } else if (typeof define == "function" && define.amd) { define("Switchery", [], function () { return require("switchery") }) } else { (this || window)["Switchery"] = require("switchery") } })();




/* Icon Picker */
(function ($) {

    $.fn.iconPicker = function (options) {
        var options = ['fa', 'fa']; // default font set
        var icons;
        $list = jQuery('');

        function font_set() {
            icons = [
                'blank',
                'adjust',
                'adn',
                'align-center',
                'align-justify',
                'align-left',
                'align-right',
                'ambulance',
                'anchor',
                'android',
                'angellist',
                'angle-double-down',
                'angle-double-left',
                'angle-double-right',
                'angle-double-up',
                'angle-down',
                'angle-left',
                'angle-right',
                'angle-up',
                'apple',
                'archive',
                'area-chart',
                'arrow-circle-down',
                'arrow-circle-left',
                'arrow-circle-o-down',
                'arrow-circle-o-left',
                'arrow-circle-o-right',
                'arrow-circle-o-up',
                'arrow-circle-right',
                'arrow-circle-up',
                'arrow-down',
                'arrow-left',
                'arrow-right',
                'arrow-up',
                'arrows',
                'arrows-alt',
                'arrows-h',
                'arrows-v',
                'asterisk',
                'at',
                'backward',
                'ban',
                'bar-chart',
                'barcode',
                'bars',
                'bed',
                'beer',
                'behance',
                'behance-square',
                'bell',
                'bell-o',
                'bell-slash',
                'bell-slash-o',
                'bicycle',
                'binoculars',
                'birthday-cake',
                'bitbucket',
                'bitbucket-square',
                'bold',
                'bolt',
                'bomb',
                'book',
                'bookmark',
                'bookmark-o',
                'briefcase',
                'btc',
                'bug',
                'building',
                'building-o',
                'bullhorn',
                'bullseye',
                'bus',
                'buysellads',
                'calculator',
                'calendar',
                'calendar-o',
                'camera',
                'camera-retro',
                'car',
                'caret-down',
                'caret-left',
                'caret-right',
                'caret-square-o-down',
                'caret-square-o-left',
                'caret-square-o-right',
                'caret-square-o-up',
                'caret-up',
                'cart-arrow-down',
                'cart-plus',
                'cc',
                'cc-amex',
                'cc-discover',
                'cc-mastercard',
                'cc-paypal',
                'cc-stripe',
                'cc-visa',
                'certificate',
                'chain-broken',
                'check',
                'check-circle',
                'check-circle-o',
                'check-square',
                'check-square-o',
                'chevron-circle-down',
                'chevron-circle-left',
                'chevron-circle-right',
                'chevron-circle-up',
                'chevron-down',
                'chevron-left',
                'chevron-right',
                'chevron-up',
                'child',
                'circle',
                'circle-o',
                'circle-o-notch',
                'circle-thin',
                'clipboard',
                'clock-o',
                'cloud',
                'cloud-download',
                'cloud-upload',
                'code',
                'code-fork',
                'codepen',
                'coffee',
                'cog',
                'cogs',
                'columns',
                'comment',
                'comment-o',
                'comments',
                'comments-o',
                'compass',
                'compress',
                'connectdevelop',
                'copyright',
                'credit-card',
                'crop',
                'crosshairs',
                'css3',
                'cube',
                'cubes',
                'cutlery',
                'dashcube',
                'database',
                'delicious',
                'desktop',
                'deviantart',
                'diamond',
                'digg',
                'dot-circle-o',
                'download',
                'dribbble',
                'dropbox',
                'drupal',
                'eject',
                'ellipsis-h',
                'ellipsis-v',
                'empire',
                'envelope',
                'envelope-o',
                'envelope-square',
                'eraser',
                'eur',
                'exchange',
                'exclamation',
                'exclamation-circle',
                'exclamation-triangle',
                'expand',
                'external-link',
                'external-link-square',
                'eye',
                'eye-slash',
                'eyedropper',
                'facebook',
                'facebook-official',
                'facebook-square',
                'fast-backward',
                'fast-forward',
                'fax',
                'female',
                'fighter-jet',
                'file',
                'file-archive-o',
                'file-audio-o',
                'file-code-o',
                'file-excel-o',
                'file-image-o',
                'file-o',
                'file-pdf-o',
                'file-powerpoint-o',
                'file-text',
                'file-text-o',
                'file-video-o',
                'file-word-o',
                'files-o',
                'film',
                'filter',
                'fire',
                'fire-extinguisher',
                'flag',
                'flag-checkered',
                'flag-o',
                'flask',
                'flickr',
                'floppy-o',
                'folder',
                'folder-o',
                'folder-open',
                'folder-open-o',
                'font',
                'forumbee',
                'forward',
                'foursquare',
                'frown-o',
                'futbol-o',
                'gamepad',
                'gavel',
                'gbp',
                'gift',
                'git',
                'git-square',
                'github',
                'github-alt',
                'github-square',
                'glass',
                'globe',
                'google',
                'google-plus',
                'google-plus-square',
                'google-wallet',
                'graduation-cap',
                'gratipay',
                'h-square',
                'hacker-news',
                'hand-o-down',
                'hand-o-left',
                'hand-o-right',
                'hand-o-up',
                'hdd-o',
                'header',
                'headphones',
                'heart',
                'heart-o',
                'heartbeat',
                'history',
                'home',
                'hospital-o',
                'html5',
                'ils',
                'inbox',
                'indent',
                'info',
                'info-circle',
                'inr',
                'instagram',
                'ioxhost',
                'italic',
                'joomla',
                'jpy',
                'jsfiddle',
                'key',
                'keyboard-o',
                'krw',
                'language',
                'laptop',
                'lastfm',
                'lastfm-square',
                'leaf',
                'leanpub',
                'lemon-o',
                'level-down',
                'level-up',
                'life-ring',
                'lightbulb-o',
                'line-chart',
                'link',
                'linkedin',
                'linkedin-square',
                'linux',
                'list',
                'list-alt',
                'list-ol',
                'list-ul',
                'location-arrow',
                'lock',
                'long-arrow-down',
                'long-arrow-left',
                'long-arrow-right',
                'long-arrow-up',
                'magic',
                'magnet',
                'male',
                'map-marker',
                'mars',
                'mars-double',
                'mars-stroke',
                'mars-stroke-h',
                'mars-stroke-v',
                'maxcdn',
                'meanpath',
                'medium',
                'medkit',
                'meh-o',
                'mercury',
                'microphone',
                'microphone-slash',
                'minus',
                'minus-circle',
                'minus-square',
                'minus-square-o',
                'mobile',
                'money',
                'moon-o',
                'motorcycle',
                'music',
                'neuter',
                'newspaper-o',
                'openid',
                'outdent',
                'pagelines',
                'paint-brush',
                'paper-plane',
                'paper-plane-o',
                'paperclip',
                'paragraph',
                'pause',
                'paw',
                'paypal',
                'pencil',
                'pencil-square',
                'pencil-square-o',
                'phone',
                'phone-square',
                'picture-o',
                'pie-chart',
                'pied-piper',
                'pied-piper-alt',
                'pinterest',
                'pinterest-p',
                'pinterest-square',
                'plane',
                'play',
                'play-circle',
                'play-circle-o',
                'plug',
                'plus',
                'plus-circle',
                'plus-square',
                'plus-square-o',
                'power-off',
                'print',
                'puzzle-piece',
                'qq',
                'qrcode',
                'question',
                'question-circle',
                'quote-left',
                'quote-right',
                'random',
                'rebel',
                'recycle',
                'reddit',
                'reddit-square',
                'refresh',
                'renren',
                'repeat',
                'reply',
                'reply-all',
                'retweet',
                'road',
                'rocket',
                'rss',
                'rss-square',
                'rub',
                'scissors',
                'search',
                'search-minus',
                'search-plus',
                'sellsy',
                'server',
                'share',
                'share-alt',
                'share-alt-square',
                'share-square',
                'share-square-o',
                'shield',
                'ship',
                'shirtsinbulk',
                'shopping-cart',
                'sign-in',
                'sign-out',
                'signal',
                'simplybuilt',
                'sitemap',
                'skyatlas',
                'skype',
                'slack',
                'sliders',
                'slideshare',
                'smile-o',
                'sort',
                'sort-alpha-asc',
                'sort-alpha-desc',
                'sort-amount-asc',
                'sort-amount-desc',
                'sort-asc',
                'sort-desc',
                'sort-numeric-asc',
                'sort-numeric-desc',
                'soundcloud',
                'space-shuttle',
                'spinner',
                'spoon',
                'spotify',
                'square',
                'square-o',
                'stack-exchange',
                'stack-overflow',
                'star',
                'star-half',
                'star-half-o',
                'star-o',
                'steam',
                'steam-square',
                'step-backward',
                'step-forward',
                'stethoscope',
                'stop',
                'street-view',
                'strikethrough',
                'stumbleupon',
                'stumbleupon-circle',
                'subscript',
                'subway',
                'suitcase',
                'sun-o',
                'superscript',
                'table',
                'tablet',
                'tachometer',
                'tag',
                'tags',
                'tasks',
                'taxi',
                'tencent-weibo',
                'terminal',
                'text-height',
                'text-width',
                'th',
                'th-large',
                'th-list',
                'thumb-tack',
                'thumbs-down',
                'thumbs-o-down',
                'thumbs-o-up',
                'thumbs-up',
                'ticket',
                'times',
                'times-circle',
                'times-circle-o',
                'tint',
                'toggle-off',
                'toggle-on',
                'train',
                'transgender',
                'transgender-alt',
                'trash',
                'trash-o',
                'tree',
                'trello',
                'trophy',
                'truck',
                'try',
                'tty',
                'tumblr',
                'tumblr-square',
                'twitch',
                'twitter',
                'twitter-square',
                'umbrella',
                'underline',
                'undo',
                'university',
                'unlock',
                'unlock-alt',
                'upload',
                'usd',
                'user',
                'user-md',
                'user-plus',
                'user-secret',
                'user-times',
                'users',
                'venus',
                'venus-double',
                'venus-mars',
                'viacoin',
                'video-camera',
                'vimeo-square',
                'vine',
                'vk',
                'volume-down',
                'volume-off',
                'volume-up',
                'weibo',
                'weixin',
                'whatsapp',
                'wheelchair',
                'wifi',
                'windows',
                'wordpress',
                'wrench',
                'xing',
                'xing-square',
                'yahoo',
                'yelp',
                'youtube',
                'youtube-play',
                'youtube-square',
                '500px',
                'amazon',
                'balance-scale',
                'battery-empty',
                'battery-full',
                'battery-half',
                'battery-quarter',
                'battery-three-quarters',
                'black-tie',
                'calendar-check-o',
                'calendar-minus-o',
                'calendar-plus-o',
                'calendar-times-o',
                'cc-diners-club',
                'cc-jcb',
                'chrome',
                'clone',
                'commenting',
                'commenting-o',
                'contao',
                'creative-commons',
                'expeditedssl',
                'firefox',
                'fonticons',
                'genderless',
                'get-pocket',
                'gg',
                'gg-circle',
                'hand-lizard-o',
                'hand-paper-o',
                'hand-peace-o',
                'hand-pointer-o',
                'hand-rock-o',
                'hand-scissors-o',
                'hand-spock-o',
                'hourglass',
                'hourglass-end',
                'hourglass-half',
                'hourglass-o',
                'hourglass-start',
                'houzz',
                'i-cursor',
                'industry',
                'internet-explorer',
                'map',
                'map-o',
                'map-pin',
                'map-signs',
                'mouse-pointer',
                'object-group',
                'object-ungroup',
                'odnoklassniki',
                'odnoklassniki-square',
                'opencart',
                'opera',
                'optin-monster',
                'registered',
                'safari',
                'sticky-note',
                'sticky-note-o',
                'television',
                'trademark',
                'tripadvisor',
                'vimeo',
                'wikipedia-w',
                'y-combinator',
                'reddit-alien',
                'edge',
                'credit-card-alt',
                'codiepie',
                'modx',
                'fort-awesome',
                'usb',
                'product-hunt',
                'scribd',
                'pause-circle',
                'pause-circle-o',
                'stop-circle',
                'stop-circle-o',
                'shopping-bag',
                'shopping-basket',
                'hashtag',
                'bluetooth',
                'bluetooth-b',
                'percent',
                'gitlab',
                'envira',
                'universal-access',
                'wheelchair-alt',
                'question-circle-o',
                'blind',
                'audio-description',
                'volume-control-phone',
                'braille',
                'assistive-listening-systems',
                'asl-interpreting',
                'american-sign-language-interpreting',
                'deafness',
                'hard-of-hearing',
                'deaf',
                'glide',
                'glide-g',
                'signing',
                'sign-language',
                'low-vision',
                'viadeo',
                'viadeo-square',
                'snapchat',
                'snapchat-ghost',
                'snapchat-square',
                'pied-piper',
                'first-order',
                'fa-yoast',
                'themeisle',
                'google-plus-circle',
                'font-awesome',
                'handshake-o',
                'envelope-open',
                'envelope-open-o',
                'linode',
                'address-book',
                'address-book-o',
                'vcard',
                'vcard-o',
                'user-circle',
                'user-circle-o',
                'user-o',
                'id-badge',
                'id-card',
                'id-card-o',
                'quora',
                'free-code-camp',
                'telegram',
                'thermometer-full',
                'thermometer-three-quarters',
                'thermometer-half',
                'thermometer-quarter',
                'thermometer-empty',
                'shower',
                'bath',
                'podcast',
                'window-maximize',
                'window-minimize',
                'window-restore',
                'window-close',
                'window-close-o',
                'bandcamp',
                'grav',
                'etsy',
                'imdb',
                'ravelry',
                'eercast',
                'microchip',
                'snowflake-o',
                'superpowers',
                'wpexplorer',
                'meetup',
            ];
            options[1] = 'fa';
        };

        font_set();

        function build_list($popup, $button, clear) {
            $list = $popup.find('.icon-picker-list');
            if (clear == 1) {
                $list.empty(); // clear list //
            }
            for (var i in icons) {
                $list.append('<li data-icon="' + icons[i] + '"><a href="#" title="' + icons[i] + '"><span class="' + options[0] + ' ' + options[1] + '-' + icons[i] + '"></span></a></li>');
            };
            $('a', $list).click(function (e) {
                e.preventDefault();
                var title = $(this).attr("title");
                if (title == 'blank') {
                    $target.closest('.menu-item').find('.preview-menu-item-icon').attr('class', 'preview-menu-item-icon');
                    $target.val('');
                } else {
                    $target.val(options[1] + "-" + title);
                    $target.closest('.menu-item').find('.preview-menu-item-icon').attr('class', 'preview-menu-item-icon fa ' + options[1] + "-" + title);
                }
                $button.removeClass().addClass("button icon-picker " + options[0] + " " + options[1] + "-" + title);
                removePopup();
            });
        };

        function removePopup() {
            $(".icon-picker-container").remove();
        }

        /*
        $button = $('.icon-picker');
        $button.each(function() {
            $(this).on('click.iconPicker', function() {
                createPopup($(this));
            });
        });
        */

        $(document).on("click", ".icon-picker", function () {
            createPopup($(this));
        });

        function createPopup($button) {
            $target = $($button.data('target'));
            $popup = $('<div class="icon-picker-container"> \
			<div class="icon-picker-control" /> \
			<ul class="icon-picker-list" /> \
		</div>');

            /*
                $popup.css({
                    'top': $button.offset().top,
                    'left': $button.offset().left
                });
            */

            build_list($popup, $button, 0);

            var $control = $popup.find('.icon-picker-control');
            $control.html('<a data-direction="back" href="#"><span class="dashicons dashicons-arrow-left-alt2"></span></a> ' +
                '<input type="text" class="" placeholder="' + tieLang.search + '" />' +
                '<a data-direction="forward" href="#"><span class="dashicons dashicons-arrow-right-alt2"></span></a>' +
                '');

            $('a', $control).click(function (e) {
                e.preventDefault();
                if ($(this).data('direction') === 'back') {
                    //move last 25 elements to front
                    $($('li:gt(' + (icons.length - 49) + ')', $list).get().reverse()).each(function () {
                        $(this).prependTo($list);
                    });
                } else {
                    //move first 25 elements to the end
                    $('li:lt(48)', $list).each(function () {
                        $(this).appendTo($list);
                    });
                }
            });

            $popup.appendTo($button.parent()).show();

            $('input', $control).on('keyup', function (e) {
                var search = $(this).val();
                if (search === '') {
                    //show all again
                    $('li:lt(48)', $list).show();
                } else {
                    $('li', $list).each(function () {
                        if ($(this).data('icon').toString().toLowerCase().indexOf(search.toLowerCase()) !== -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
            });

            $(document).mouseup(function (e) {
                if (!$popup.is(e.target) && $popup.has(e.target).length === 0) {
                    removePopup();
                }
            });
        }
    }

    $(function () {
        $('.icon-picker').iconPicker();
    });

}(jQuery));


/* Get Color Brightes */
function getContrastColor(hexcolor) {
    hexcolor = hexcolor.replace('#', '');
    var r = parseInt(hexcolor.substr(0, 2), 16);
    var g = parseInt(hexcolor.substr(2, 2), 16);
    var b = parseInt(hexcolor.substr(4, 2), 16);
    var yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
    return (yiq >= 128) ? 'dark' : 'light';
}

