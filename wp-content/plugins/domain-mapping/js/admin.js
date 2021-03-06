(function($) {
	function cleanup_alert(callback) {
		$('#domainmapping-ppp, #domainmapping-ppp-overlay').fadeOut(50, function() {
			$('#domainmapping-ppp').remove();
			$('#domainmapping-ppp-overlay').remove();
		});

		if ($.isFunction(callback)) {
			callback();
		}
	}

	function show_alert(msg, callback, classes) {
		var ppp, body, footer, overflow, close;

		body = $('<div id="domainmapping-ppp-body"></div>');
		body.html(msg);

		close = $('<button type="button" class="button domainmapping-button"></button>');
		close.append(domainmapping.button.close);
		close.click(function() {
			cleanup_alert(callback);
		});

		footer = $('<div id="domainmapping-ppp-footer"></div>');
		footer.append(close);

		ppp = $('<div id="domainmapping-ppp"></div>');
		ppp.addClass(classes);
		ppp.append(body);
		ppp.append(footer);

		overflow = $('<div id="domainmapping-ppp-overlay"></div>"');

		cleanup_alert();
		$('body').append(ppp, overflow);

		ppp.css({
			'margin-left': '-' + (ppp.outerWidth() / 2) + 'px',
			'margin-top': '-' + (ppp.outerHeight() / 2) + 'px'
		});

		$('#domainmapping-ppp, #domainmapping-ppp-overlay').fadeIn(50);

		close.focus();
	};

	function show_success(msg, callback) {
		show_alert(msg, callback, 'domainmapping-ppp-success');
	};

	function show_error(msg, callback) {
		show_alert(msg, callback, 'domainmapping-ppp-error');
	};


	$(document).ready(function() {
		var $domains = $('.domainmapping-domains');

		$('#domainmapping-front-mapping select').change(function() {
			var form = $(this).parents('form'),
                $spinner = $("#domainmapping-front-mapping-spinner");

            $spinner.css({visibility: "visible"});
			$.post(form.attr('action'), form.serialize(), function(){
                $spinner.css({visibility: "hidden"});
            });
		});

		$('#domainmapping-form-map-domain').on("submit", function() {
			var self = this,
				$self = $(self),
				domain = $.trim($self.find('.domainmapping-input-domain').val()),
				wrapper = $self.parents('.domainmapping-domains-wrapper');

			if (domain) {
				wrapper.addClass('domainmapping-domains-wrapper-locked');

				$.post($self.attr('action'), $self.serialize(), function(response) {
					wrapper.removeClass('domainmapping-domains-wrapper-locked');

					if (response.success == undefined) {
						return;
					}

					if (response.success) {
                        $(".domainmapping-domains-list li").not(".domainmapping-form").last().after(response.data.html);
                        $(".domainmapping-front-mapping-form-row").show();
						self.reset();
					} else {
						if (response.data.message) {
							show_error(response.data.message);
						}
					}

					if (response.data.hide_form) {
						wrapper.addClass('domainmapping-form-hidden');
					}

					$('a.domainmapping-need-revalidate').click();
				});
			} else {
				show_error(domainmapping.message.empty);
			}

			return false;
		});

		$domains.on('click', 'a.domainmapping-map-remove', function() {
			var $self = $(this),
				parent = $self.parent(),
                $tr = $self.closest("tr"),
				wrapper = $self.parents('.domainmapping-domains-wrapper'),
                $dropdown_row = $(".domainmapping-front-mapping-form-row");

			if (confirm(domainmapping.message.unmap)) {
				$.get($self.attr('data-href'), {}, function(response) {
                    if( response.success ){
                        parent.fadeOut(300, function() {
                            parent.remove();
                            if (response && response.data && response.data.show_form) {
                                wrapper.removeClass('domainmapping-form-hidden');
                            }
                        });
                        $tr.fadeOut(300, function() {
                            $tr.remove();
                        });

                     if( $(".domainmapping-domains-list li").not(".domainmapping-form").length === 2 ){
                         $dropdown_row.fadeOut(300);
                     }
                    }else{
                        show_error(domainmapping.message.unmap_error);
                    }

				});
			}

			return false;
		});

		$domains.on('click', 'a.domainmapping-map-state', function() {
			var $self = $(this),
				parent = $self.parent();

			$self.hide();
			parent.addClass('domainmapping-wait-status-refresh');

			$.get($self.attr('href'), {}, function(response) {
				parent.removeClass('domainmapping-wait-status-refresh');
				if (response.success != undefined && response.success) {
					$self.replaceWith(response.data.html);
				}
				$self.show();
			});

			return false;
		});

        /**
         * Toggles domain is_primary attribute
         */
		$domains.on('click', 'a.domainmapping-map-primary', function() {
			var $this = $(this),
                message,
                interval,
                animate = function (){
                    interval = setInterval(function(){
                        $this.toggleClass('dashicons-star-empty dashicons-star-filled');
                    }, 350);

                },
                stop_animation = function(){
                    //clearInterval(interval);
                };

            animate();
			if ($this.hasClass('dashicons-star-empty')) {
				message = $this.parents('li').find('a.domainmapping-map-state').hasClass('domainmapping-valid-domain')
					? domainmapping.message.valid_selection
					: domainmapping.message.invalid_selection;

				if (confirm(message)) {
					$domains.find('a.domainmapping-map-primary.dashicons-star').toggleClass('dashicons-star-filled dashicons-star-empty');
					$.get($this.attr('data-select-href'), {}, function(data){
                        clearInterval(interval);
                        if( data &&  data.success ){
                            $this.removeClass('dashicons-star-empty');
                            $this.addClass('dashicons-star-filled');
                        }else{
                            $this.addClass('dashicons-star-empty');
                            $this.removeClass('dashicons-star-filled');
                        }

                    });
				}
			} else {
				if (confirm(domainmapping.message.deselect)) {
					//$this.toggleClass('dashicons-star-filled dashicons-star-empty');
					$.get($this.attr('data-deselect-href'), {}, function(data){
                        clearInterval(interval);
                        if( data &&  data.success ){
                            $this.addClass('dashicons-star-empty');
                            $this.removeClass('dashicons-star-filled');
                        }else{
                            $this.removeClass('dashicons-star-empty');
                            $this.addClass('dashicons-star-filled');
                        }
                    });
				}
			}

			return false;
		});


		$('a.domainmapping-need-revalidate').click();

		$('.domainmapping-reseller-switch').change(function() {
			$('.domainmapping-reseller-settings').hide();
			$('#reseller-' + $(this).val()).show();
		});

		$('#domainmapping-check-domain-form').submit(function() {
			var $self = $(this),
				domain = $.trim($self.find('.domainmapping-input-domain').val()),
				wrapper = $self.parents('.domainmapping-domains-wrapper');

			if (domain) {
				wrapper.addClass('domainmapping-domains-wrapper-locked');
				$.post($self.attr('action'), $self.serialize(), function(response) {
					wrapper.removeClass('domainmapping-domains-wrapper-locked');

					if (response.success == undefined) {
						return;
					}

					if (response.success) {
						wrapper.find('.domainmapping-form-results').html(response.data.html);
					} else {
						if (response.data.message) {
							show_error(response.data.message);
						}
					}
				});
			} else {
				show_error(domainmapping.message.empty);
			}

			return false;
		});

		$('.domainmapping-tab').on('submit', '#domainmapping-iframe-form', function() {
			var $this = $(this),
				wrapper = $this.parents('.domainmapping-domains-wrapper'),
				card_number = $this.find('#card_number').val(),
				card_expiry = $this.find('#card_expiration').payment('cardExpiryVal'),
				card_type = null;

			if (!$.payment.validateCardNumber(card_number)) {
				show_error(domainmapping.message.invalid.card_number);
				return false;
			}

			if (!card_expiry.month || !card_expiry.year || !$.payment.validateCardExpiry(card_expiry.month, card_expiry.year)) {
				show_error(domainmapping.message.invalid.card_expiry);
				return false;
			}

			card_type = $.payment.cardType(card_number);
			$this.find('#card_type').val(card_type);
			if (card_type === null) {
				show_error(domainmapping.message.invalid.card_type);
				return false;
			}

			if (!$.payment.validateCardCVC($this.find('#card_cvv2').val(), card_type)) {
				show_error(domainmapping.message.invalid.card_cvv);
				return false;
			}

			wrapper.addClass('domainmapping-domains-wrapper-locked');

			return true;
		});

		if ($.payment != undefined) {
			$('#domainmapping-box-iframe #card_number').payment('restrictNumeric').payment('formatCardNumber');
			$('#domainmapping-box-iframe #card_expiration').payment('formatCardExpiry');
			$('#domainmapping-box-iframe #card_cvv2').payment('formatCardCVC');
		}
	});

    /**
     * WHMCS
     */

    /**
     * Client Login
     */
    $(document).on("submit", "#dm_whmcs_client_login", function( e ){
        e.preventDefault();
       var $this = $(this),
           email = $("#dm_client_email").val(),
           password = $("#dm_client_pass").val(),
           sld = $(".domainmapping-input-domain").val(),
           tld = $(".domainmapping-select-domain").val(),
           $wrapper = $this.parents('.domainmapping-domains-wrapper');

        if( email.length < 3 || password.length < 3 ) {
            show_error(domainmapping.message.empty_email_pass);
        }

        $wrapper.addClass('domainmapping-domains-wrapper-locked');
        $.ajax({
            type           : "post",
            url            : ajaxurl ,
            data           :{
                action     : "dm_whmcs_validate_client_login",
                data       : {
                    email : email,
                    password2 :  password ,
                    tld : tld,
                    sld : sld
                }
            },
            success        : function( result ){
                if( result.success === true ){
                    $(".domainmapping-info.domainmapping-info-success").data("form_html", $(".domainmapping-info.domainmapping-info-success").html() );
                    $(".domainmapping-info.domainmapping-info-success").html( result.data.html );
                }else if( typeof  result.data !== "undefined"){

                    show_error( result.data.error );
                }
                $wrapper.removeClass('domainmapping-domains-wrapper-locked');
            },
            error          : function( result ){
                $wrapper.removeClass('domainmapping-domains-wrapper-locked');
            }
        });
    });

    /**
     * Add domain pricing row
     */
    $("#dm_whmcs_tlds_add_row a").on("click", function(e){
        e.preventDefault();
       var $new_row = $(".dm_whmcs_tlds tbody tr").first().clone(),
           num_rows = $(".dm_whmcs_tlds tbody tr").length;

        $new_row.find("input").each(function( index ){
            var current_name = $(this).attr("name");

            if( index === 0 ){
                $(this).attr("name", current_name.replace("[0]", "[" + ( num_rows - 1 ) +"]"));
            }else{
                $(this).attr("name", current_name.replace("[0][", "[" + ( num_rows - 1 ) +"]["));
            }
            $(this).val("");
        });
        $(this).closest("tr").before($new_row);
        $(".dm_whmcs_tlds_remove_row").show();
        $(".dm_whmcs_tlds_remove_row").attr("disabled", false);
    });

    /**
     * Add domain pricing column
     */
    $("#dm_whmcs_tlds_add_col a").on("click", function( e ){
        e.preventDefault();
       var $new_col = $(".dm_whmcs_tlds thead th").eq(1).clone(),
           num_cols = $(".dm_whmcs_tlds thead th").length;
        $new_col.find(".dm_year_count").html( num_cols - 1 );
        // adding header
        $(this).closest("th").before($new_col);

        // adding rows
        $(".dm_whmcs_tlds tbody tr").not(".inactive_row").each(function(){
            var $price_cell = $(this).find(".dm_whmcs_price_cell").first().clone(),
                num_cells = $(this).find(".dm_whmcs_price_cell").length;
            $price_cell.val("");

            $price_cell.attr("name", $price_cell.attr("name").replace("][0]", "][" + ( num_cells  ) +  "]" ));
            $(this).find(".inaactive_cell").first().before( $price_cell );
            $price_cell.wrap("<td></td>");
        });
        $(".dm_whmcs_tlds_remove_col").attr("disabled", false);
        $(".dm_whmcs_tlds_remove_col").show();
    });

    /**
     * Remove domain pricing row
     */
    $(document).on("click", ".dm_whmcs_tlds_remove_row", function( e ){
        e.preventDefault();

        if( $( ".dm_whmcs_tlds_remove_row").length === 1 ) return;

        $(this).closest("tr").toggle("highlight", function(){
            $(this).remove();
            if( $(".dm_whmcs_tlds_remove_row").length === 1  ){
                $(".dm_whmcs_tlds_remove_row").attr("disabled", true);
                $(".dm_whmcs_tlds_remove_row").hide();
            }
        });
    });

    /**
     * Remove domain pricing column
     */
    $(document).on("click", ".dm_whmcs_tlds_remove_col", function( e ){
        e.preventDefault();
        // if this is the only col remove button, don't remove
        if( $(".dm_whmcs_tlds_remove_col").length === 1 ) return;
        var $this_header = $(this).closest("th"),
            index = $this_header.index();
        console.log(index);
       $(".dm_whmcs_tlds tbody tr").each(function(){
           $("td", this).eq(index).toggle("highlight", function(){
               $(this).remove();
           });
           $this_header.toggle("highlight", function(){
               $(this).remove();
               // if there is one col left, disable and hide the remove buttons
               if( $(".dm_whmcs_tlds_remove_col").length === 1){
                   $(".dm_whmcs_tlds_remove_col").hide();
                   $(".dm_whmcs_tlds_remove_col").attr("disabled", true);
               }

               // take care of column year labels
               $(".dm_year_count").each(function(index){
                  $(this).text(index + 1);
               });
           });
       })
    });

    /**
     * Domain order cancel
     */
    $(document).on("click", "#dm-whmcs-domain-order-cancel", function(e){
        e.preventDefault();
        var form_html = $(".domainmapping-info.domainmapping-info-success").data("form_html");
        $(".domainmapping-info.domainmapping-info-success").html( form_html );
    });

    /**
     * Domain ordering submit
     */
    $(document).on("submit", "#domainmapping-whmcs-order-form", function( e ){
        e.preventDefault();
        var $this = $(this),
            sld = $("input[name='sld']").val(),
            tld = $("input[name='tld']").val(),
            period = $("#dm_whmcs_domain_period").val(),
            $wrapper = $this.parents('.domainmapping-domains-wrapper'),
            form_html = $(".domainmapping-info.domainmapping-info-success").data("form_html");
        if( sld && tld && period ){
            $wrapper.addClass('domainmapping-domains-wrapper-locked');
        }else{
            show_error( domainmapping.message.invalid_data );
            $(".domainmapping-info.domainmapping-info-success").html( form_html );
            return;
        }
        $.ajax({
            type           : "post",
            url            : ajaxurl ,
            data           :{
                action     : "dm_whmcs_order_domain",
                data       : {
                    period : period,
                    tld : tld,
                    sld : sld
                }
            },
            success        : function( result ){
                if( result.success === true ){ // ordering successful
                    show_success( domainmapping.message.order.success  );
                }else if( typeof  result.data !== "undefined" && result.data.expired ){ // client id not found or expired
                    show_error( result.data.message );
                }else if( result.data !== "undefined" && result.data.message )  { // ordering failed
                    show_error( result.data.message );
                }else{
                    show_error( domainmapping.message.order.failed );
                    $wrapper.removeClass('domainmapping-domains-wrapper-locked');
                    return;
                }

                $(".domainmapping-info.domainmapping-info-success").html( form_html );
                $wrapper.removeClass('domainmapping-domains-wrapper-locked');
            },
            error          : function( result ){
                $wrapper.removeClass('domainmapping-domains-wrapper-locked');
            }
        });
    });

    /**
     * ======================================================================================
     * WHMCS register new client
     */
    $(document).on("click", "#dm_whmcs_register_client", function( e ){
        e.preventDefault();

        $(".domainmapping-info.domainmapping-info-success").data("form_html", $(".domainmapping-info.domainmapping-info-success").html() );

        var $this = $(this),
            url = $this.attr("href"),
            $wrapper = $this.parents('.domainmapping-domains-wrapper');

        $wrapper.addClass('domainmapping-domains-wrapper-locked');

        $.ajax({
            type           : "get",
            url            : url ,
            complete       : function(){
                $wrapper.removeClass('domainmapping-domains-wrapper-locked');
            },
            success        : function( result ){
                $(".domainmapping-info.domainmapping-info-success").html( result );
            }
        });
    });

    /**
     * Cancel registration
     */
    $(document).on("click", "#dm_whmcs_registeration_cancel", function( e ){
        e.preventDefault();
        var form_html = $(".domainmapping-info.domainmapping-info-success").data("form_html");
        $(".domainmapping-info.domainmapping-info-success").html( form_html );
    });

    function validate_client_form(){
        var errs = [];

        $("#dm-whmcs-client-registration-form input, #dm-whmcs-client-registration-form select").each(function(index){
           var $this = $(this),
               id = this.id,
               error_id = "#" +  id + "_err",
               val = $this.val();
            if( val === "" ){
                $(error_id).css("display", "block");
                errs.push(index);
            }else{
                var i = errs.indexOf( index );
                if( i !== -1 ){
                    delete errs[i];
                }

                $(error_id).css("display", "none");
            }
        });

        if( $("#account_password").val() !==  $("#account_password_confirm").val() ){
            $("#account_password_match_err").css("display", "block");
            errs.push("pass");
        }else{
            $("#account_password_match_err").css("display", "none");

            var i = errs.indexOf( "pass" );
            if( i !== -1 ){
                delete errs[i];
            }
        }

        /**
         * Scroll to the first error
         */
        if( errs.length ){
            var $first_el = $(".domainmapping-info-error").filter(function(index, el){
                return $(el).css("display") === "block";
            });
            if( $first_el ){
                $('html, body').animate({ scrollTop: ($first_el.offset().top - 90) }, 600);
            }

        }
        return +! errs.length;
    }

    $(document).off("submit", "#dm-whmcs-client-registration-form").on("submit", "#dm-whmcs-client-registration-form", function(e){
        e.preventDefault();
        var $wrapper = $('.domainmapping-domains-wrapper'),
            $form = $(this);
            form_html = $(".domainmapping-info.domainmapping-info-success").data("form_html");
        $wrapper.addClass('domainmapping-domains-wrapper-locked');

        if( validate_client_form() ){
            $.ajax({
                type           : "post",
                url            : ajaxurl ,
                data           :{
                    action     : "dm_whmcs_register_client",
                    data       : $form.serialize(),
                    sld        : $(".domainmapping-input-domain").val(),
                    tld        : $(".domainmapping-select-domain").val()
                },
                success        : function( result ){

                    if( result.success === true ){ // registering successful
                        show_success( domainmapping.message.registration.success  );
                        $(".domainmapping-info.domainmapping-info-success").html( result.data.html  );
                    }else{
                        show_error( result.data.message + "\n" + result.data.errors );
                    }

                    $wrapper.removeClass('domainmapping-domains-wrapper-locked');
                },
                error          : function( result ){
                    $wrapper.removeClass('domainmapping-domains-wrapper-locked');
                }
            });
        }else{
            $wrapper.removeClass('domainmapping-domains-wrapper-locked');
        }
    });


    /**
     * Toggles domain scheme
     */
    $(document).on('click', 'a.domainmapping-map-toggle-scheme', function( e ) {
        var $this = $(this),
            $link = $this.closest("li").length ? $this.closest("li").find(".domainmapping-mapped") : $this.closest("tr").find(".domainmapping-mapped"),
            current_link = $link.html(),
            href = $this.data("href"),
            $spinner = $(".spinner").first().clone().removeAttr("id").css({ visibility: "visible", marginTop: 0 });

        e.preventDefault();

        $this.closest("li").find(".spinner").remove();
        $link.append( $spinner.show() );
        $.ajax({
            type        : "get",
            url         : href,
            success     : function(res){
                $spinner.remove();
                if( res.success ){
                    current_link = current_link.replace("<del>", "");
                    current_link = current_link.replace("</del>", "");


                    current_link.replace("https://", "http://");
                    if( res.data.schema === 1 ){
                        current_link = current_link.replace("http://", "https://");
                    }else if( res.data.schema === 2 ){
                        current_link = current_link.replace("https://", "http://");
                        current_link = current_link.replace("http://", "<del>http://</del>");
                    }

                    $link.toggle("highlight");
                    $link.html( current_link );
                    $link.toggle("highlight");

                }

            }
        })
    });

    $("input[name='map_crossautologin']").on("change", function(){

        var $this = $(this),
            $child_list = $(".domainmapping-child-list-crossautologin");
        if( $this.val() === "1"){
            $child_list.slideDown();
        }else{
            $child_list.slideUp();
        }
    });

    var pages_checkbox = function(field_selector, label_selector  ){
        this.$field = $(field_selector);
        this.$label = $(label_selector);

        this.remove_page = function(page_id){
            var excluded_pages = this.$field.val().replace(/ /g,'').split(","),
                page_id_index = excluded_pages.indexOf(page_id.toString());

            excluded_pages.splice(page_id_index, 1);
            this.$field.val( excluded_pages.join(",") );
            this.update_label();
        };

        this.add_page =  function(page_id){
            var excluded_pages = $.isEmptyObject( this.$field.val() ) ? [] : this.$field.val().replace(/ /g,'').split(",");
            this.$field.val( excluded_pages.concat([page_id]).join(",") );
            this.update_label();
        };

        this.update_label = function(){
            var ids  = this.$field.val().trim() == "" ? [] : this.$field.val().trim().split(",");

            this.$label.text( ids.length  );
        }
    };

    var excluded_pages = new pages_checkbox( "#dm_exluded_pages_hidden_field", ".dm_excluded_pages_label span" );
    var ssl_forced_pages = new pages_checkbox( "#dm_ssl_forced_pages_hidden_field", ".dm_ssl_forced_pages_label span" );

    $(document).on("change", ".dm_excluded_page_checkbox", function(){

        var $this = $(this),
            id = $this.data("id");

        if( $this.is(":checked") ){
            excluded_pages.add_page( id );
        }else{
            excluded_pages.remove_page( id );
        }

    });

    $(document).on("change", ".dm_ssl_forced_page_checkbox", function(){

        var $this = $(this),
            id = $this.data("id");

        if( $this.is(":checked") ){
            ssl_forced_pages.add_page( id );
        }else{
            ssl_forced_pages.remove_page( id );
        }

    });

})(jQuery);


(function($) {
    var list = {
        init: function() {
            var timer;
            var delay = 500;
            $('.domainmapping-box .tablenav-pages a, .domainmapping-box .manage-column.sortable a, .domainmapping-box .manage-column.sorted a').on('click', function(e) {
                e.preventDefault();
                var query = this.search.substring( 1 );

                var data = {
                    paged: list.__query( query, 'paged' ) || '1',
                    order: list.__query( query, 'order' ) || 'asc',
                    orderby: list.__query( query, 'orderby' ) || 'title'
                };
                list.update( data );
            });
            // Page number input
            $('input[name=paged]').on('keyup', function(e) {

                if ( 13 == e.which )
                    e.preventDefault();
                var data = {
                    paged: parseInt( $('input[name=paged]').val() ) || '1',
                    order: $('input[name=order]').val() || 'asc',
                    orderby: $('input[name=orderby]').val() || 'title'
                };

                window.clearTimeout( timer );
                timer = window.setTimeout(function() {
                    list.update( data );
                }, delay);
            });

            $("#dm_excluded_pages_search_form").on("submit", function(e){
                e.preventDefault();
                var s_val =  $("#dm_excluded_pages_search_s").val();
                //if( !s_val.length ) return;
                var data = {
                    paged: parseInt( $('input[name=paged]').val() ) || '1',
                    order: $('input[name=order]').val() || 'asc',
                    orderby: $('input[name=orderby]').val() || 'title',
                    s: s_val
                };
                window.clearTimeout( timer );
                timer = window.setTimeout(function() {
                    list.update( data );
                }, delay);
            })
        },

        update: function( data ) {
            var $spinner = $("#dm_excluded_pages_search_spinner"),
                $excluded_pages =  $("#dm_exluded_pages_hidden_field"),
                get_excluded_pages_ids = function(){
                    return $excluded_pages.length ?  $excluded_pages.val().replace(/ /g,'').split(",") : [];
                };

            $.ajax({
                url: ajaxurl,
                data: $.extend(
                    {
                        _excluded_pages_nonce: $('#_excluded_pages_nonce').val(),
                        action: 'update_excluded_pages_list'
                    },
                    data
                ),
                beforeSend:  function(){
                    $spinner.show();
                },
                complete:  function(){
                    $spinner.hide();
                },
                // Handle the successful result
                success: function( response ) {
                    // WP_List_Table::ajax_response() returns json
                    var response = $.parseJSON( response );

                    if ( response.rows.length )
                        $('#the-list').html( response.rows );

                    if ( response.column_headers.length )
                        $('thead tr, tfoot tr').html( response.column_headers );

                    if ( response.pagination.bottom.length )
                        $('.tablenav.top .tablenav-pages').html( $(response.pagination.top).html() );
                    if ( response.pagination.top.length )
                        $('.tablenav.bottom .tablenav-pages').html( $(response.pagination.bottom).html() );

                    /**
                     * Keep checkboxes in sync
                     */
                    var excluded_pages = get_excluded_pages_ids();
                    $(".dm_excluded_page_checkbox").each(function(){
                       var $this = $(this),
                           id = $this.data("id").toString();

                        if( excluded_pages.indexOf( id ) !== -1 ){
                            $this.prop('checked', true);
                        }else{
                            $this.prop('checked', false);
                        }
                    });
                    list.init();
                }
            });
        },

        __query: function( query, variable ) {
            var vars = query.split("&");
            for ( var i = 0; i <vars.length; i++ ) {
                var pair = vars[ i ].split("=");
                if ( pair[0] == variable )
                    return pair[1];
            }
            return false;
        }
    };
    list.init();
})(jQuery);