jQuery(document).ready(function($) {
	
	//Tooltips
	jQuery('#cff-admin .cff-tooltip-link').click(function(){
		jQuery(this).closest('tr, h3, div').find('.cff-tooltip').slideToggle();
	});

	//Toggle Access Token field
	if( jQuery('#cff_show_access_token').is(':checked') ) jQuery('.cff-access-token-hidden').show();
	jQuery('#cff_show_access_token').change(function(){
		jQuery('.cff-access-token-hidden').fadeToggle();
	});


	//Is this a page, group or profile?
	var cff_page_type = jQuery('.cff-page-type select').val(),
		$cff_page_type_options = jQuery('.cff-page-options'),
		$cff_profile_error = jQuery('.cff-profile-error.cff-page-type'),
		$cff_group_error = jQuery('.cff-group-error.cff-page-type');

	//Should we show anything initially?
	if(cff_page_type !== 'page') $cff_page_type_options.hide();
	if(cff_page_type == 'profile') $cff_profile_error.show();
	if(cff_page_type == 'group') $cff_group_error.show();

	//When page type is changed show the relevant item
	jQuery('.cff-page-type').change(function(){
		cff_page_type = jQuery('.cff-page-type select').val();

		if( cff_page_type !== 'page' ) {
			$cff_page_type_options.hide();
			if( cff_page_type == 'profile' ) {
					$cff_profile_error.show();
					$cff_group_error.hide();
				} else if( cff_page_type == 'group' ) {
					$cff_group_error.show();
					$cff_profile_error.hide();
				} else {
					$cff_group_error.hide();
					$cff_profile_error.hide();
				}
			
		} else {
			$cff_page_type_options.show();
			$cff_profile_error.hide();
			$cff_group_error.hide();
		}
	});


	//Post limit manual setting
	var cff_limit_setting = jQuery('#cff_limit_setting').val(),
			cff_post_limit = jQuery('#cff_post_limit').val(),
			$cff_limit_manual_settings = jQuery('#cff_limit_manual_settings');
	if( typeof cff_post_limit === 'undefined' ) cff_post_limit = '';

	//Should we show anything initially?
	if(cff_limit_setting == 'auto') $cff_limit_manual_settings.hide();
	if(cff_post_limit.length > 0){
		$cff_limit_manual_settings.show();
		jQuery('#cff_limit_setting').val('manual');
	}

	jQuery('#cff_limit_setting').change(function(){
		cff_limit_setting = jQuery('#cff_limit_setting').val();

		if(cff_limit_setting == 'auto'){
			$cff_limit_manual_settings.hide();
			jQuery('#cff_post_limit').val('');
		} else {
			$cff_limit_manual_settings.show();
		}
	});


	//Header icon
	//Icon type
	//Check the saved icon type on page load and display it
	jQuery('#cff-header-icon-example').removeClass().addClass('fa fa-' + jQuery('#cff-header-icon').val() );
	//Change the header icon when selected from the list
	jQuery('#cff-header-icon').change(function() {
	    var $self = jQuery(this);

	    jQuery('#cff-header-icon-example').removeClass().addClass('fa fa-' + $self.val() );
	});


	//Test Facebook API connection button
	jQuery('#cff-api-test').click(function(e){
		e.preventDefault();
		//Show the JSON
		jQuery('#cff-api-test-result textarea').css('display', 'block');
	});
	

	//If '__ days ago' date is selected then show 'Translate this'
	var $cffTranslateDate = jQuery('#cff-translate-date');

	if ( jQuery("#cff-date-formatting option:selected").val() == '1' ) $cffTranslateDate.show();
	
	jQuery("#cff-date-formatting").change(function() {
		if ( jQuery("#cff-date-formatting option:selected").val() == '1' ) {
			$cffTranslateDate.fadeIn();
		} else {
			$cffTranslateDate.fadeOut();
		}
	});

	//Selecting a post style
	jQuery('.cff-post-style').click(function(){
        var $self = jQuery(this);
        $('.cff_post_style').trigger('change');
        $self.addClass('cff-layout-selected').find('#cff_post_style').attr('checked', 'checked');
        $self.siblings().removeClass('cff-layout-selected');
    });
    function cffChangePostStyleSettings() {
        setTimeout(function(){
            jQuery('.cff-post-style-settings').hide();
            jQuery('.cff-post-style-settings.cff-'+jQuery('.cff_post_style:checked').val()).show();
        }, 1);
    }
    cffChangePostStyleSettings();
    jQuery('.cff_post_style').change(cffChangePostStyleSettings);

	//Add the color picker
	if( jQuery('.cff-colorpicker').length > 0 ) jQuery('.cff-colorpicker').wpColorPicker();


	//Mobile width
	var cff_feed_width = jQuery('#cff-admin #cff_feed_width').val(),
			$cff_width_options = jQuery('#cff-admin #cff_width_options');

	if (typeof cff_feed_width !== 'undefined') {
		//Show initially if a width is set
		if(cff_feed_width.length > 1 && cff_feed_width !== '100%') $cff_width_options.show();

		jQuery('#cff_feed_width').change(function(){
			cff_feed_width = jQuery(this).val();

			if( cff_feed_width.length < 2 || cff_feed_width == '100%' ) {
				$cff_width_options.slideUp();			
			} else {
				$cff_width_options.slideDown();
			}
		});
	}

	//Scroll to hash for quick links
	jQuery('#cff-admin a').click(function() {
	if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
	  var target = jQuery(this.hash);
	  target = target.length ? target : this.hash.slice(1);
	  if (target.length) {
	    jQuery('html,body').animate({
	      scrollTop: target.offset().top
	    }, 500);
	    return false;
	  }
	}
	});

	//Shortcode tooltips
	jQuery('#cff-admin label').click(function(){
	  	var $el = jQuery(this);
	    var $cff_shortcode = $el.siblings('.cff_shortcode');
	    if($cff_shortcode.is(':visible')){
	      $el.siblings('.cff_shortcode').css('display','none');
	    } else {
	      $el.siblings('.cff_shortcode').css('display','block');
	    }  
	});
	jQuery('#cff-admin th').hover(function(){
		if( jQuery(this).find('.cff_shortcode').length > 0 ){
		  jQuery(this).find('label').append('<code class="cff_shortcode_symbol">[]</code>');
		}
	}, function(){
		jQuery(this).find('.cff_shortcode_symbol').remove();
	});
	jQuery('#cff-admin label').hover(function(){
		if( jQuery(this).siblings('.cff_shortcode').length > 0 ){
		  jQuery(this).attr('title', 'Click for shortcode option');
		}
	}, function(){});

	//Open/close the expandable option sections
	jQuery('.cff-expandable-options').hide();
	jQuery('.cff-expand-button a').on('click', function(e){
		e.preventDefault();
		var $self = jQuery(this);
		$self.parent().next('.cff-expandable-options').toggle();
		if( $self.text().indexOf('Show') !== -1 ){
			$self.text( $self.text().replace('Show', 'Hide') );
		} else {
			$self.text( $self.text().replace('Hide', 'Show') );
		}
	});

	//Facebook login
	$('#cff_fb_login').on('click', function(){
		$('#cff_fb_login_modal').show();
	});
	$('#cff_admin_cancel_btn').on('click', function(){
		$('#cff_fb_login_modal').hide();
	});
	$('.cff-modal-close, #cff-close-modal-primary-button').on('click', function(){
		$('.cff_modal_tokens').hide();
	});
	$('#cff_fb_show_tokens').on('click', function(){
		$('.cff_modal_tokens, .cff-groups-list').show();
		$('#cff-group-installation').hide();
	});

	//Select a page for token
	$('.cff-managed-page').on('click', function(){
		$('#cff-insert-token, .cff-insert-reviews-token, .cff-insert-both-tokens').removeAttr('disabled');

		$('#cff_token_expiration_note').show();

		var $self = $(this);
		if( $self.hasClass('cff-page-selected') ){
			$self.removeClass('cff-page-selected');
		} else {
			$self.addClass('cff-page-selected');
		}
	});

	



	//Connect Accounts array object
	var cff_connected_accounts = {},
		cff_multifeed_enabled = false,
		cff_remove_primary_text = 'Remove as Primary Feed',
    	cff_add_primary_text = 'Make Primary Feed';

    if( $('#cff_page_id').hasClass('cff_multifeed_enabled') ) cff_multifeed_enabled = true;
    if( cff_multifeed_enabled ){
    	cff_remove_primary_text = 'Remove from Primary Feed';
    	cff_add_primary_text = 'Add to Primary Feed';
    }

	//If there are accounts displayed then assign them to the connected accounts array
	var cff_connected_accounts_val = $('#cff_connected_accounts').val();
	if( cff_connected_accounts_val !== '' && cff_connected_accounts_val !== '{}' && typeof cff_connected_accounts_val !== 'undefined' ){

		cff_connected_accounts = cff_connected_accounts_val.replace(/\\"/g, '"');
		cff_connected_accounts = JSON.parse(cff_connected_accounts);

		createAccountHTML(cff_connected_accounts);
	}

	//Insert Page Access Token
	$('#cff-insert-token, #cff-insert-all-tokens').on('click', function(){

		if( $(this).hasClass('cff_connect_all') ) $('.cff-managed-page').addClass('cff-page-selected');

		var $selectedPage = $('.cff-page-selected'),
			selectedPageId = $selectedPage.attr('data-page-id'),
			selectedPageToken = $selectedPage.attr('data-token');

		//Add ID to setting
		if( $('#cff_page_id').val().trim() == '' ){
			$('#cff_page_id').val( selectedPageId ).addClass('cff-success');
			cffAddCurIdLabel($('.cff-page-selected').first().find('.cff-page-info-name').text(), $('.cff-page-selected').first().find('.cff-page-avatar').attr('src'));
		}

		//Add token to setting
		if( $('#cff_access_token').val().trim() == '' || $('#cff_access_token').hasClass('cff-replace-token') ){
			//If multifeed then add ID to front so it's assigned to that ID in the feed
			if( $('#cff_page_id').hasClass('cff_multifeed_enabled') ) selectedPageToken = selectedPageId + ':' + selectedPageToken;

			$('#cff_access_token').val( selectedPageToken ).addClass('cff-success');
		}

		if( $(this).hasClass('cff-group-btn') ){
			$('.cff-groups-list').hide();
			$('#cff-group-installation').show();

			//Show directions for either group admin or member
			if( $('.cff-page-selected').hasClass('cff-group-admin') ){
				$('#cff-group-admin-directions').show();
				$('#cff-group-member-directions').hide();
			} else {
				$('#cff-group-admin-directions').hide();
				$('#cff-group-member-directions').show();
			}

			//Change page type to be group
			$('#cff_page_type').val('group');
			$('.cff-page-options').hide();

			//Dynamically create group edit link
			var cffGroupEditLink = 'https://facebook.com/groups/'+selectedPageId+'/edit';
			$('#cff-group-installation #cff-group-edit').attr('href', cffGroupEditLink);
		} else {
			$('.cff_modal_tokens').hide();
		}

		// cff_connected_accounts
		$('.cff-managed-pages').find('.cff-page-selected').each(function(){
			var $page = $(this);

			addConnectedAccounts(
				$page.attr('data-page-id'),
				$page.find('.cff-page-info-name').text(),
				$page.attr('data-pagetype'),
				$page.attr('data-token'),
				$page.find('.cff-page-avatar').attr('src')
			);

		});

		location.hash = "cffnomodal";
	});

	//Manually connect account
	//Step 1
	$('#cff_manual_account_button, #cff-admin .cff_manual_back').on('click', function(e){
		e.preventDefault();
		if( !$(this).hasClass('cff_manual_back') ) $('#cff_manual_account').toggle();
		$('#cff_manual_account_step_1').show();
		$('#cff_manual_account_step_2').hide();
	});
	//Step 2
	jQuery("#cff_manual_account_type").change(function() {
		cff_go_to_step_2();
	});
	$('#cff-admin .cff_manual_forward').on('click', function(){
		if( $("#cff_manual_account_type option:selected").val() ){
			cff_go_to_step_2();
		} else {
			$("#cff_manual_account_type").addClass('cff_error');
			setTimeout(function(){ $("#cff_manual_account_type").removeClass('cff_error'); }, 500);
		}
	});
	function cff_go_to_step_2(){
		$('#cff_manual_account_step_2').attr('class', 'cff_account_type_'+jQuery("#cff_manual_account_type option:selected").val() );

		$('#cff_manual_account_step_1').hide();
		$('#cff_manual_account_step_2').show();
	}

	//Add account
	$('#cff_manual_account_step_2 input[type=submit]').on('click', function(e){
		e.preventDefault();

		var $cff_manual_account = $('#cff_manual_account');

		addConnectedAccounts(
			$cff_manual_account.find('#cff_manual_account_id').val(),
			$cff_manual_account.find('#cff_manual_account_name').val(),
			$cff_manual_account.find('#cff_manual_account_type').val(),
			$cff_manual_account.find('#cff_manual_account_token').val(),
			false
		);
	});

	//Only enable manual account submit button if ID/token fields have values
	$('#cff_manual_account_id, #cff_manual_account_token').on('input', function() {
		if( $('#cff_manual_account_id').val() == '' || $('#cff_manual_account_token').val() == '' ){
			$('#cff_manual_account_step_2 #submit').attr('disabled', true);
		} else {
			$('#cff_manual_account_step_2 #submit').removeAttr('disabled');
		}
	});

	//Show raw account data (can be used for exporting/importing accounts in bulk)
	$('#cff_export_accounts').on('click', function(e){
		e.preventDefault();
		$('#cff_export_accounts_wrap').toggle();
	});


	function addConnectedAccounts(id, name, pagetype, accesstoken, avatar=false){

		if( pagetype == 'page' ) avatar = '';

		id = cffStripURLParts(id);

		//Add to connected accounts array
		cff_connected_accounts[id] = {
			id: id,
			name: encodeURI( name ),
			pagetype: pagetype,
			accesstoken: accesstoken,
			avatar: avatar
		};

		//Update setting on page
		$('#cff_connected_accounts').val( JSON.stringify(cff_connected_accounts) );

		//Add HTML to page
		createAccountHTML(cff_connected_accounts);
	}

	function removeConnectedAccount($account){		
		//Remove account from array
		delete cff_connected_accounts[$account.attr('data-page-id')];

		//Update setting on page
		$('#cff_connected_accounts').val( JSON.stringify(cff_connected_accounts) );

		//Remove it from primary feed if it's in there
		removePrimaryAcount($account);

		//Remove account element from page
		$account.remove();
	}

	function cffStripURLParts(string){
		if (typeof string === 'undefined') {
			return '';
		}
		//If user pastes their full URL into the Page ID field then strip it out
		var cff_facebook_string = 'facebook.com',
			hasURL = (string.indexOf(cff_facebook_string) > -1);
		if (hasURL) {
			var stringArr = string.split('?')[0].replace(/\/$/, '').split('/');
			string = stringArr[stringArr.length-1].replace(/[\.\/]/,'');
		}

		return string;
	}

	function createAccountHTML(cff_connected_accounts){

		var accountsHTML = '';

		//Loop through accounts and create HTML
		for (var key in cff_connected_accounts) {
		    if (cff_connected_accounts.hasOwnProperty(key)) {

		        var id = cffStripURLParts(cff_connected_accounts[key]['id']),
		        	name = decodeURI(cff_connected_accounts[key]['name']),
		        	pagetype = cff_connected_accounts[key]['pagetype'],
		        	accesstoken = cff_connected_accounts[key]['accesstoken'],
		        	avatar = cff_connected_accounts[key]['avatar'],
		        	cff_account_active = '',
		        	no_avatar = false;

		        if( (!avatar || avatar == 'false' ) && pagetype == 'group' ) no_avatar = true;
		        if( !avatar || avatar == '' ) avatar = 'https://graph.facebook.com/'+id+'/picture';

		        //If it's in use then mark it as primary/active
		        if( $('#cff_page_id').val().indexOf(id) !== -1 ) cff_account_active = ' cff_account_active';

		        accountsHTML += '<div class="cff_connected_account cff_account_type_'+pagetype+cff_account_active+'" id="cff_connected_account_'+id+'" data-accesstoken="'+accesstoken+'" data-pagetype="'+pagetype+'" data-page-id="'+id+'">' +
                    '<div class="cff_ca_info">' +
                        '<div class="cff_ca_delete"><a href="JavaScript:void(0);" class="cff_delete_account"><i class="fa fa-times"></i><span class="cff_remove_text">Remove</span></a></div>'+
                        '<div class="cff_ca_username">';
                        ( no_avatar ) ? accountsHTML += '' : accountsHTML += '<img class="cff_ca_avatar" src="'+avatar+'">';
							accountsHTML += '<strong><span class="cff_ca_fullname">'+name+'</span><span class="cff_ca_pagetype">'+pagetype+' ID: '+id+'</span></strong>' +
                        '</div>' +
                        '<div class="cff_ca_actions">' +
							'<a href="JavaScript:void(0);" class="cff_make_primary">';
							if( cff_account_active !== '' ){
								accountsHTML += '<i class="fa fa-minus-circle" aria-hidden="true"></i>'+cff_remove_primary_text;
							} else {
								accountsHTML += '<i class="fa fa-plus-circle" aria-hidden="true"></i>'+cff_add_primary_text;
							}
							accountsHTML += '</a>';

							if( $('#cff_page_access_token').length && pagetype == 'page' ) accountsHTML += '<a href="JavaScript:void(0);" class="cff_make_reviews"><i class="fa fa-star" aria-hidden="true"></i>Use for Reviews Feed</a>';

							accountsHTML += '<a class="cff_ca_token_shortcode" href="JavaScript:void(0);"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i>Add to another Feed</a>' +
                            '<p class="cff_ca_show_token"><a href="javascript:void(0);" id="cff_ca_show_token_'+id+'"><i class="fa fa-ellipsis-h" style="margin: 0; font-size: 12px;" aria-hidden="true"></i></a></p>' +
                        '</div>' +
                        '<div class="cff_ca_shortcode">' +
                            '<p>Copy and paste this shortcode into your page or widget area:<br>' +
                                '<code>[custom-facebook-feed account="'+id+'"]</code>' +
                            '</p>';
                        if( cff_multifeed_enabled ) accountsHTML += '<p>To add multiple accounts in the same feed, simply separate them using commas:<br>' +
								'<code>[custom-facebook-feed account="'+id+', account_2, account_3"]</code>' +
                            '</p>';
                        accountsHTML += '<p>Click <a href="https://smashballoon.com/custom-facebook-feed/docs/shortcodes/" target="_blank">here</a> to learn more about shortcodes</p>' +
                        '</div>' +
                        '<div class="cff_ca_accesstoken">' +
                            '<span class="cff_ca_token_label">Access Token:</span><input type="text" class="cff_ca_token" value="'+accesstoken+'" readonly="readonly" onclick="this.focus();this.select()" title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac).">' +
                        '</div>' +
                    '</div>' +
                '</div>';
		    }
		}

		//Add HTML to page
		$('#cff_connected_accounts_wrap').html(accountsHTML);

		//Add Raw Data button
		$('.cff_connected_actions').show();

	}

	function removePrimaryAcount($account){
		//Remove ID/token from fields
    	if( $account.hasClass('cff_account_active') ){

    		var selected_id = $account.attr('data-page-id'),
        		selected_token = $account.attr('data-accesstoken');

    		//Remove as primary account
    		cffLabelAsPrimary($account);

    		$('#cff_primary_account_label').hide();

    		if( cff_multifeed_enabled ){

    			//Find the ID from the removed account and remove it from the ID field
    			var updatedIdVal = $('#cff_page_id').val().replace(selected_id, '');
    			//Remove any stray commas left over
    			updatedIdVal = updatedIdVal.replace(',,', '').replace(' ,', '').replace(/^, |, $/g,'');

        		$('#cff_page_id').val( updatedIdVal ).removeClass('cff-success');

        		//Remove Token
        		// var updatedTokenVal = $('#cff_access_token').val().replace(selected_id+':'+selected_token, '').replace(selected_token, '');
        		var updatedTokenVal = $('#cff_access_token').val().replace(selected_id+':'+selected_token, '');
        		//Remove any stray commas left over
    			updatedTokenVal = updatedTokenVal.replace(',,', '').replace(' ,', '').replace(':,', ':').replace(/^, |, $/g,'');

        		$('#cff_access_token').val( updatedTokenVal ).removeClass('cff-success');


        	} else {

        		//Revert ID/token fields back to previous values
        		$('#cff_page_id').val( $('#cff_page_id').attr('data-page-id') ).removeClass('cff-success');
        		$('#cff_access_token').val( $('#cff_access_token').attr('data-accesstoken') ).removeClass('cff-success');

        	}
    	}
	}


	var $body = $('body');
	//Show Access Token
	$body.on('click', '.cff_ca_show_token a', function(e) {
		e.preventDefault();
        jQuery(this).closest('.cff_ca_info').find('.cff_ca_accesstoken').slideToggle(200);
    });
    $body.on('click', '.cff_ca_token_shortcode, .cff_make_primary, .cff_make_reviews', function (event) {
        event.preventDefault();
        var $clicked = $(event.target);
        //Show shortcode
        if( $clicked.hasClass('cff_ca_token_shortcode') ) {
            jQuery(this).closest('.cff_ca_info').find('.cff_ca_shortcode').slideToggle(200);
        }
        //Make Reviews account
        if( $clicked.hasClass('cff_make_reviews') ){
        	$('#cff_page_access_token').val( $clicked.closest('.cff_connected_account').attr('data-accesstoken') ).addClass('cff-success');
        }
        //Make primary account
        if( $clicked.hasClass('cff_make_primary') ){
        	var $selected_account = $clicked.closest('.cff_connected_account'),
        		selected_id = $selected_account.attr('data-page-id'),
        		selected_token = $selected_account.attr('data-accesstoken');


        	//Remove ID/token from fields
        	if( $selected_account.hasClass('cff_account_active') ){
        		
        		removePrimaryAcount($selected_account);

	        //Add ID/token to fields
        	} else {

        		//Add as primary account
        		cffLabelAsPrimary($selected_account, true);

	        	//Add ID/token to fields
	        	if( cff_multifeed_enabled ){

	        		//Add ID to existing IDs already in field
	        		var id_sep = ', ',
	        			existing_id = $('#cff_page_id').val().trim(),
	        			existing_token = $('#cff_access_token').val().trim();

	        		if( existing_id == '' ) id_sep = '';
	        		$('#cff_page_id').val( existing_id + id_sep + selected_id ).addClass('cff-success');

	        		//Change to multiple token format
	        		var token_format = '';
	        		if( existing_token !== '' ) token_format += existing_token + ', ';
	        		token_format += selected_id + ':' + selected_token;

	        		$('#cff_access_token').val( token_format ).addClass('cff-success');

	        	} else {

	        		//Replace existing ID and token
	        		$('#cff_page_id').val( selected_id ).addClass('cff-success');
	        		$('#cff_access_token').val( selected_token ).addClass('cff-success');

	        		//Remove active account class from other accounts
	        		$selected_account.siblings().each(function(){
	        			cffLabelAsPrimary($(this));
	        		});

	        	}
        	

        	}
        }
    });
    //Remove account
    $body.on('click', '.cff_delete_account', function(){
        removeConnectedAccount( $(this).closest('.cff_connected_account') );
    });

    //Change button label when adding/removing as primary account
    function cffLabelAsPrimary($account, makePrimary=false){
    	if( makePrimary ){
        	$account.addClass('cff_account_active').find('.cff_make_primary').html('<i class="fa fa-minus-circle" aria-hidden="true"></i>'+cff_remove_primary_text);

        	if( $account.length > 0 ) cffAddCurIdLabel($account.find('.cff_ca_fullname').text(), $account.find('.cff_ca_avatar').attr('src'));

    	} else {
    		$account.removeClass('cff_account_active').find('.cff_make_primary').html('<i class="fa fa-plus-circle" aria-hidden="true"></i>'+cff_add_primary_text);
    	}
    }

    function cffAddCurIdLabel(name, avatar){
    	var account_img = '',
    		account_name = '<span>' + name + '</span>';
    	if( avatar !== undefined ) account_img = '<img src="' + avatar + '" />';

    	$('#cff_primary_account_label').show().html( account_img + account_name );
    }

    //Label a primary account when page is first loaded
    var cff_current_page_id = $('#cff_page_id').val(),
    	cff_current_page_id_arr = [];
    if( typeof cff_current_page_id !== 'undefined' ) var cff_current_page_id_arr = cff_current_page_id.split(',');

    if( cff_current_page_id_arr.length > 1 ){
    	for (var i = 0; i < cff_current_page_id_arr.length; i++) {
		    cffLabelAsPrimary( $('#cff_connected_account_' + cffValidateID( cff_current_page_id_arr[i] ) ), true );
		}
    } else {
    	cffLabelAsPrimary( $('#cff_connected_account_' + cffValidateID( cff_current_page_id ) ), true );
    }
    //Make sure ID is a valid string
    function cffValidateID(id){
    	if( typeof id === 'undefined' || id == '' ) return;

    	//Remove slashes from end
    	id = cffStripURLParts( id.replace(/\/$/, "").trim() );
    	//Only return if it contains numbers/letters
    	if( id.match("^[A-Za-z0-9]+$") ) return id;
    }

	//Show the modal by default, but hide if the "cffnomodal" class is added to prevent it showing after saving settings
	if( location.hash !== '#cffnomodal' ){
		$('.cff_modal_tokens').removeClass('cffnomodal');
	}

	//Switch Page/Group app button in modal
	jQuery("#cff_login_type").change(function() {
		if ( jQuery("#cff_login_type option:selected").val() == 'group' ) {
			jQuery('#cff_page_app').hide();
			jQuery('#cff_group_app').css('display', 'inline-block');
		} else {
			jQuery('#cff_page_app').css('display', 'inline-block');
			jQuery('#cff_group_app').hide();
		}
	});

    //Load the admin share widgets
    $('#cff-admin-show-share-links').on('click', function(){
    	$(this).fadeOut();
        if( $('#cff-admin-share-links iframe').length == 0 ) $('#cff-admin-share-links').html('<a href="https://twitter.com/share" class="twitter-share-button" data-url="https://wordpress.org/plugins/custom-facebook-feed/" data-text="Display your Facebook posts on your site your way using the Custom Facebook Feed WordPress plugin!" data-via="smashballoon" data-dnt="true">Tweet</a> <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");</script> <style type="text/css"> #twitter-widget-0{float: left; width: 82px !important;}.IN-widget{margin-right: 20px;}</style> <div id="fb-root" style="display: none;"></div><script>(function(d, s, id){var js, fjs=d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js=d.createElement(s); js.id=id; js.src="//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.0"; fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script> <div class="fb-like" data-href="https://wordpress.org/plugins/custom-facebook-feed/" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true" style="display: block; float: left; margin-right: 5px;"></div><script src="//platform.linkedin.com/in.js" type="text/javascript"> lang: en_US </script> <script type="IN/Share" data-url="https://wordpress.org/plugins/custom-facebook-feed/"></script></div>');

        setTimeout(function(){
        	$('#cff-admin-share-links').addClass('cff-show');
        }, 500);
    });

    //Group app setup screenshot
	jQuery('#cff-group-app-tooltip').hover(function(){
	    jQuery('#cff-group-app-screenshot').fadeIn(100);
	}, function(){
		jQuery('#cff-group-app-screenshot').fadeOut(100);
	});

	//Remove any duplicate groups
	jQuery('.cff-group-admin').each(function(){
		jQuery('.cff-groups-list #' + jQuery(this).attr('id') ).eq(1).hide();
	});


	//Show/hide mobile column setting
    var cff_masonry_desktop_col = jQuery('#cff_cols').val(),
		$cff_mobile_col_settings = jQuery('.cff-mobile-col-settings');
	if( typeof cff_post_limit === 'undefined' ) cff_masonry_desktop_col = '1';

	//Should we show anything initially?
	if( cff_masonry_desktop_col == '1' ) $cff_mobile_col_settings.hide();
	if( parseInt(cff_masonry_desktop_col) > 1 ){
		$cff_mobile_col_settings.show();
	}

	jQuery('#cff_cols').change(function(){
		cff_cols_num = parseInt( jQuery('#cff_cols').val() );

		if(cff_cols_num > 1){
			$cff_mobile_col_settings.slideDown(200);
		} else {
			$cff_mobile_col_settings.slideUp(200);
		}
	});

	// notices

	if (jQuery('#cff-notice-bar').length) {
		jQuery('#wpadminbar').after(jQuery('#cff-notice-bar'));
		jQuery('#wpcontent').css('padding-left', 0);
		jQuery('#wpbody').css('padding-left', '20px');
		jQuery('#cff-notice-bar').show();
	}

	jQuery('#cff-notice-bar .dismiss').click(function(e) {
		e.preventDefault();
		jQuery('#cff-notice-bar').remove();
		jQuery.ajax({
			url: cffA.ajax_url,
			type: 'post',
			data: {
				action : 'cff_lite_dismiss',
				cff_nonce: cffA.cff_nonce
			},
			success: function (data) {
			}
		});
	});

});