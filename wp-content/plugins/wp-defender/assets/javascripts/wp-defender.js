jQuery(function ($) {
    // Add event handlers to show overlay dialogs.
    WDefender.hardener();
    WDefender.scanning();
    WDefender.blackList();
    WDefender.resolve();
    WDefender.dashboard();
    WDefender.audit_logging();
    WDefender.ip_lockout();

    WDefender.settings();
    $('.wd-dismiss').click(function (e) {
        e.preventDefault();
        $(this).closest('div').fadeOut(500, function () {
            $(this).remove();
        });
    });
    $('.toggle-checkbox').each(function () {
        var parent = $(this).parent();
        var hidden = parent.find('input[type="hidden"]');
        var element = parent.find('input[type="checkbox"]');
        if (hidden.size() == 0) {
            hidden = $('<input type="hidden"/>');
            $(this).parent().append(hidden);
            hidden.attr('name', element.attr('id'));
            hidden.val(element.prop('checked') == true ? 1 : 0);
        }
        $(this).click(function () {
            if (element.prop('checked') == true) {
                hidden.val(1);
            } else {
                hidden.val(0);
            }
        })
    });
    $('body').on('after_an_issue_resolved', function (event, count) {
        if ($('.wd-issue-indicator-sidebar').size() > 0) {
            var current = $('.wd-issue-indicator-sidebar').first().text();
            if (current.length == 0) {
                current = 0;
            }
            current = parseInt(current) + parseInt(count);
            if (current == 0) {
                $('.wd-issue-indicator-sidebar span').remove();
            } else {
                if ($('.wd-issue-indicator-sidebar span').size() == 0) {
                    $('.wd-issue-indicator-sidebar').append('<span></span>');
                }

                $('.wd-issue-indicator-sidebar span').text(current);
            }
        }
    })
})
jQuery.fn.wd_according = function (options) {
    var $ = jQuery;
    var settings = $.extend({
        'fixed_color': '#1ABC9C',
        'issue_color': '#FECF2F',
        'open_color': '#F4F4F4'
    }, options);
    var elements = [];
    this.each(function () {
        var target = $($(this).data('target'));
        elements.push($(this));
        target.hide();
        var that = $(this);
        that.unbind('click');
        that.on('click', function () {
            if (that.hasClass('open')) {
                close_according($(this), target);
            } else {
                var opener = find_open();
                if (opener) {
                    close_according(opener);
                }
                open_according($(this), target);
            }
        })
    });

    function determine_background_color(element) {
        if (element.hasClass('fixed')) {
            return settings.fixed_color;
        } else {
            return settings.issue_color;
        }
    }

    function open_according(element, target) {
        var icon = element.find('.wd_toggle_icon');
        var current_y = $(window).scrollTop();

        if (target == undefined) {
            target = $(element.data('target'));
        }
        target.slideDown({
            start: function () {
                element.animate({
                    backgroundColor: '#F4F4F4'
                }, 500);
                icon.wdrotate({
                    animateTo: 45
                });
            },
            progress: function (animate, progress, remain) {
                $("html,body").scrollTop(current_y);
            },
            complete: function () {
                element.addClass('open');
                element.find('.wd-caret-down').removeClass('wd-hide');
            }
        })
    }

    function find_open() {
        var ret = null;
        $.each(elements, function (i, v) {
            var target = $($(this).data('target'));
            if (target.is(':visible')) {
                ret = $(this);
                return;
            }
        })
        return ret;
    }

    function close_according(element, target) {
        var icon = element.find('.wd_toggle_icon');
        var current_y = $(window).scrollTop();
        if (target == undefined) {
            target = $(element.data('target'));
        }
        target.slideUp({
            start: function () {
                var bg_color = determine_background_color(element);
                element.animate({
                    backgroundColor: bg_color
                });
                icon.wdrotate({
                    animateTo: 0
                });
            },
            progress: function (animate, progress, remain) {
                $("html,body").scrollTop(current_y);
            },
            complete: function () {
                element.removeClass('open');
                element.find('.wd-caret-down').addClass('wd-hide');
            }
        })
    }
};

window.WDefender = window.WDefender || {};

WDefender.ip_lockout = function (el) {
    var $ = jQuery;
    $('.toggle-ip-protect').click(function (e) {
        e.preventDefault();
        var that = $(this);
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'ip_protection_toggle',
                type: that.data('type')
            },
            beforeSend: function () {
                that.attr('disabled', 'disabled');
            },
            success: function (data) {
                if (data.status == 1) {
                    var html = data.value;
                    that.replaceWith(html);
                }
            }
        })
    })
}

WDefender.hardener = function (el) {
    var $ = jQuery;

    function listen_to_url() {
        var pathname = window.location.href;
        var parts = pathname.split('#');
        if (parts.length == 2) {
            var id = parts[1];
            var element = $('[data-target="#' + id + '"]');
            if (element.size() > 0) {
                element.trigger('click');
                $('html, body').animate({
                    scrollTop: element.offset().top
                }, 1000);
            }
        }
    }

    function listening_to_complete() {
        if ($('.wd-hardener-summary').size() > 0) {
            $(document).ajaxComplete(function (event, xhr, settings) {
                var data = settings.data;
                data = data.split('&');
                $.each(data, function (i, v) {
                    var parts = v.split('=');
                    if ($.trim(parts[0]) == 'action') {
                        if (parts[1].substring(0, 3) == 'wd_') {
                            //gotcha, now need to check if the issues list is empty
                            setTimeout(function () {
                                //as animation usually 500ms, we need a delay of 550
                                if ($('.wd-hardener-error').find('.wd-hardener-rule').size() == 0) {
                                    location.reload();
                                } else {
                                    //update the counter
                                    $('.wd-indicator.yellow strong').text($('.wd-hardener-error').find('.wd-hardener-rule').size());
                                    $('.wd-indicator.green strong').text($('.wd-hardener-success').find('.wd-hardener-rule').size());
                                }
                            }, 550);
                        }
                    }
                })
            });
        }
    }

    if ($('.wd-according').size() > 0) {
        $('.wd-according').wd_according();
    }

    function init() {
        listen_to_url();
        listening_to_complete();
    }

    init();

}

WDefender.audit_logging = function () {
    var jq = jQuery;

    function load_events() {
        if (jq('#audit-table-content').size() > 0) {
            _load_events(_get_form_data(jq('.wd-audit-filter form')));

            jq('body').on('click', '.audit-nav', function (e) {
                e.preventDefault();
                if (jq(this).attr('disabled') != undefined) {
                    return;
                }
                var url = jq(this).attr('href');
                var params = url.split('?');
                delete params[0];
                params = params.join('');
                if (params.indexOf('wd_audit_get_events') == -1) {
                    params += '&action=wd_audit_get_events';
                }
                if (state == 1) {
                    _load_events(params);
                }
            });

            jq('body').on('change', '.wd-event-filter input[type="checkbox"]', function () {
                var query = _get_form_data(jq('.wd-audit-filter form'));
                if (state == 1) {
                    _load_events(query);
                }
            })

            jq('body').on('click', '#col_date a', function (e) {
                e.preventDefault();
                var url = jq(this).attr('href');
                var params = url.split('?');
                delete params[0];
                params = params.join('');
                if (state == 1) {
                    _load_events(params);
                }
            });
            jq('.wd-audit-filter form').submit(function (e) {
                e.preventDefault();
                var that = jq(this);
                var params = that.serialize();
                params += '&action=wd_audit_get_events';
                console.log(params);
                if (state == 1) {
                    _load_events(params);
                }
                return false;
            });
            jq('#wd_user_id').on('item:select', function () {
                var query = _get_form_data(jq('.wd-audit-filter form'));
                if (state == 1) {
                    _load_events(query);
                }
            })
            jq('#wd_user_id').on('item:clear', function () {
                jq(this).val('');
                var query = _get_form_data(jq('.wd-audit-filter form'));
                if (state == 1) {
                    _load_events(query);
                }
            });
            /*jq.on('results:clear', function () {
             console.log(13);
             });*/
        }
    }

    var current_url = null;
    var current_count = 0;
    var timeout = null;
    var state = 0;

    function _get_form_data($form) {
        $inputs = $form.find(':input');
        var data = [];
        $inputs.each(function () {
            if (jq(this).attr('name') != undefined) {
                if (jq(this).attr('type') == 'checkbox') {
                    if (jq(this).prop('checked') == true) {
                        data.push(jq(this).attr('name') + '=' + jq(this).val());
                    }
                } else if (jq(this).attr('name') == 'date_from') {
                    var val = jq(this).val();
                    val = val.split(' - ');
                    if (val.length == 2) {
                        data.push('date_from=' + val[0]);
                        data.push('date_to=' + val[1])
                    } else {
                        data.push('date_from=' + val);
                    }
                } else {
                    data.push(jq(this).attr('name') + '=' + jq(this).val());
                }
            }
        })
        data.push('action=wd_audit_get_events');
        data = data.join('&');
        return data;
    }

    function _load_events($query) {
        var url = ajaxurl + '?' + $query;
        if (jq('#audit-table-content').find('table').size() > 0 || jq('#audit-table-content').find('.wd-well').size() > 0) {
            var overlay = WDefender.createOverlay(jq('#audit-table-content'));
        } else {
            var overlay = null;
        }
        if (timeout != null) {
            clearTimeout(timeout);
        }
        state = 0;
        jq.ajax({
            type: 'GET',
            url: url,
            beforeSend: function () {
                if (overlay != null) {
                    overlay.appendTo(jq('#audit-table-content'));
                }
                jq('.wd-audit-filter form').find(':input').attr('disabled', 'disabled')
            },
            success: function (data) {
                jq('#audit-table-content').html(data.table);
                jq('#audit-table-nav').html(data.nav);
                if (overlay != null) {
                    overlay.remove();
                }
                current_url = url;
                var html = jq(data.nav);
                current_count = html.find('.num-results').data('count');
                lsiten_to_new_event();
                state = 1;
                jq('.wd-audit-filter form').find(':input').removeAttr('disabled')
            }
        })
    }

    function lsiten_to_new_event() {
        timeout = setTimeout(function () {
            jq.ajax({
                type: 'GET',
                url: current_url,
                success: function (data) {
                    var html = jq(data.nav);
                    var server_count = html.find('.num-results').data('count');
                    if (server_count > current_count) {
                        var parent = jq('#wd-audit-table').parent();

                        var odd = parseInt(server_count) - parseInt(current_count);
                        var icon = jq('<i/>').attr({
                            'class': 'wdv-icon wdv-icon-fw wdv-icon-refresh'
                        });
                        var text = '';
                        if (odd == 1) {
                            text = ' ' + audit_logging.load_event_text_singular;
                        } else {
                            text = ' ' + audit_logging.load_event_text_plural;
                        }
                        text = text.replace('%s', odd);
                        if (parent.find('.wd-new-events-alert').size() > 0) {
                            parent.find('.wd-new-events-alert').html(text);
                            parent.find('.wd-new-events-alert').prepend(icon);
                        } else {
                            var a = jq('<a/>');
                            var components = current_url.split('?');
                            var queries = components[1];
                            var lindex = -1;
                            queries = queries.split('&');
                            jq.each(queries, function (i, v) {
                                if (v.indexOf('paged=') != -1) {
                                    lindex = i;
                                    return;
                                }
                            })
                            delete queries[lindex];
                            queries = queries.join('&');
                            var new_url = components[0] + '?' + queries;
                            a.attr({
                                'class': 'wd-new-events-alert block button button-grey audit-nav',
                                'href': new_url
                            });
                            a.html(text);
                            a.prepend(icon);
                            a.hide();
                            parent.prepend(a);
                            a.fadeIn(500);
                        }
                    }
                    lsiten_to_new_event();
                }
            })
        }, 20000);
    }

    function toggle_status() {
        jq('body').on('submit', '.wd_audit_status_toggle', function () {
            var that = jq(this);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    that.find('button').attr('disabled', 'disabled');
                    that.find('.wd-error').html('').addClass('wd-hide');
                },
                success: function (data) {
                    if (data.status == 1) {
                        location.reload();
                    } else {
                        that.find('button').removeAttr('disabled');
                        that.find('.wd-error').html(data.error).removeClass('wd-hide');
                    }
                }
            })
            return false;
        })
    }

    function listening_to_user_filter() {
        if (jq("#wd_user_id").size() > 0) {
            var typingTimer;                //timer identifier
            var doneTypingInterval = 1000;  //time in ms, 5 second for example
            var $input = jq("#wd_user_id");

            //on keyup, start the countdown
            $input.on('keyup', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(doneTyping, doneTypingInterval);
            });

            //on keydown, clear the countdown
            $input.on('keydown', function () {
                clearTimeout(typingTimer);
            });

            //user is "finished typing," do something
            function doneTyping() {
                //do something
                var that = $input;
                var value = that.val();
                if (value.length > 2) {
                    jq.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: {
                            'action': 'wd_audit_suggest_user_name',
                            'term': value,
                            'get_all': 1
                        },
                        beforeSend: function () {
                            that.trigger('progress:start');
                        },
                        success: function (data) {
                            data = jq.parseJSON(data);
                            that.trigger('progress:stop');
                            that.trigger('results:show', [data]);
                        }
                    })
                }

                jq('#wd_user_id').on('item:select', function () {

                })
            }

            //bind data if have any
            //jq('#wd_user_id').selectItem(jq('<li data-id="admin" class="item item-admin"><span class="thumb" style="background-image:url(http://2.gravatar.com/avatar/8df7301ce776ac597ab96ebe8ce5f3f9?s=96&amp;d=mm&amp;r=g)"></span><span class="item-label"><span class="name title">admin</span> <span class="email">hoang1213@gmail.com</span></span></li>'));
            if (audit_logging.user_label != undefined) {
                var curitem = jq('.current-item');
                curitem.html(audit_logging.user_label);
                curitem.show();
                $input.hide();
                $input.trigger('item:select')
            }
        }
    }

    function toggle_email_report() {
        jq('body').on('submit', '.setup-email-report-form', function () {
            var that = jq(this);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    that.find('button').attr('disabled', 'disabled');
                    that.find('.wd-error').html('').addClass('wd-hide');
                },
                success: function (data) {
                    if (data.status == 1) {
                        jq('.wd-audit-log-information').html(data.html);
                        that.find('button').removeAttr('disabled');
                        location.reload();
                    } else {
                        that.find('button').removeAttr('disabled');
                        that.find('.wd-error').html(data.error).removeClass('wd-hide');
                    }
                }
            })
            return false;
        })
    }

    function toggle_audit_log() {
        jq('#toggle_audit_log').change(function () {
            jq('.wd_audit_status_toggle').submit();
        })
    }

    function toggle_email_report_slide() {
        jq('#toggle_audit_email_report').change(function () {
            jq(this).closest('h3').find('.setup-email-report-form').submit();
        })
    }

    function init() {
        load_events();
        toggle_status();
        toggle_email_report();
        listening_to_user_filter();
        if (jq('.wd-calendar').size() > 0) {
            var start = moment().subtract(7, 'days');
            var end = moment();
            var maxDate = end;
            var minDate = moment().subtract(90, 'days');
            jq('#wd_range_from').daterangepicker({
                //startDate: start,
                //endDate: end,
                autoApply: true,
                maxDate: maxDate,
                minDate: minDate,
                showDropdowns: false,
                applyClass: 'wd-hide',
                cancelClass: 'wd-hide',
                alwaysShowCalendars: true,
                opens: 'center',
                dateLimit: {
                    days: 14
                },
                locale: {
                    "format": "MM/DD/YYYY",
                    "separator": " - "
                },
                template: '<div class="daterangepicker wd-calendar dropdown-menu"> ' +
                '<div class="ranges"> ' +
                '<div class="range_inputs"> ' +
                '<button class="applyBtn" disabled="disabled" type="button"></button> ' +
                '<button class="cancelBtn" type="button"></button> ' +
                '</div> ' +
                '</div> ' +
                '<div class="calendar left"> ' +
                '<div class="calendar-table"></div> ' +
                '</div> ' +
                '<div class="calendar right"> ' +
                '<div class="calendar-table"></div> ' +
                '</div> ' +
                '</div>',
                showCustomRangeLabel: false,
                ranges: {
                    'Today': [moment(), moment()],
                    '7 Days': [moment().subtract(6, 'days'), moment()],
                    '30 Days': [moment().subtract(29, 'days'), moment()]
                }
            }).on('apply.daterangepicker', function (ev, picker) {
                var query = _get_form_data(jq('.wd-audit-filter form'));
                if (state == 1) {
                    _load_events(query);
                }
            });
        }

        toggle_audit_log();
        toggle_email_report_slide();
    }

    init();
}

WDefender.settings = function () {
    var jq = jQuery;

    function init_tags(element) {
        var data = element.val();
        if (data == '[]') {
            data = [];
        } else {
            data = data.split(',');
        }
        element.val('');
        var args = {
            plugins: 'tags'
        }
        if (data.length > 0) {
            args.tags = {
                items: data
            }
        }
        element.wdtextext(args);
    }

    if (jq('.wd-tags').size() > 0) {
        init_tags(jq('.wd-tags'));
    }
    function split(val) {
        return val.split(/,\s*/);
    }

    function extractLast(term) {
        return split(term).pop();
    }

    if (jq("#email-recipient").size() > 0) {
        jq("#email-recipient").bind("keyup change", function (e) {
            var value = jq(this).val();
            var that = jq(this);
            if (value.length > 2) {
                jq.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action': 'wd_suggest_user_name',
                        'term': value
                    },
                    beforeSend: function () {
                        that.trigger('progress:start');
                    },
                    success: function (data) {
                        data = jq.parseJSON(data);
                        that.trigger('progress:stop');
                        that.trigger('results:show', [data]);
                    }
                })
            }
        });

        jq('#email-recipient').on('item:select', function () {
            jq(this).closest('form').find('button').removeAttr('disabled');
        })
    }

    jq('#email-recipients-frm').submit(function () {
        var that = jq(this);
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: that.serialize(),
            beforeSend: function () {
                that.find('button').attr('disabled', 'disabled').css({
                    'cursor': 'wait'
                });
                that.find('.wd-error').hide();
            },
            success: function (data) {
                if (data.status == 1) {
                    jq('#wd-recipients').replaceWith(jq(data.html));
                    that.find('input[type="text"]').val('');
                    that.find('button').removeAttr('disabled').css({
                        'cursor': 'pointer'
                    });
                } else {
                    that.find('.wd-error').html(data.error).show();
                    that.find('button').removeAttr('disabled').css({
                        'cursor': 'pointer'
                    });
                }
                jq("#email-recipient").trigger('results:clear');
                jq("#email-recipient").val('');
            }
        });
        return false;
    });
    jq('body').on('click', '.wd-remove-recipient', function (e) {
        e.preventDefault();
        var that = jq(this);
        jq.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action': 'wd_remove_recipient',
                'id': that.data('id')
            },
            beforeSend: function () {
                that.closest('div').fadeOut(1000).remove();
            }
        })
    })
}

WDefender.resolve = function () {
    var jq = jQuery;
    var prev_trigger = null;

    function ajax_inline_update() {
        jq('body').on('submit', '.wd-resolve-plugins-update', function () {
            var that = jq(this);
            var parent = jq(this).closest('.wd-scan-resolve-dialog');
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    that.find('button').attr('disabled', 'disabled').html(that.find('button').text() + ' <i class="wdv-icon wdv-icon-fw wdv-icon-refresh spin"></i>')
                    parent.find('.wd-error').html('').addClass('wd-hide');
                },
                success: function (data) {
                    if (data.success == true) {
                        WDP.closeOverlay();
                        if (prev_trigger != null) {
                            var tr = prev_trigger.closest('tr');
                            tr.fadeOut(500, function () {
                                tr.remove();
                            })
                        }
                    } else {
                        parent.find('.wd-error').html(data.data.error).removeClass('wd-hide');
                        that.find('button').removeAttr('disabled').html(that.find('button').text().replace(' <i class="wdv-icon wdv-icon-fw wdv-icon-refresh spin"></i>'))
                    }
                }
            })
            return false;
        })
    }

    function resolve_action_submit() {
        jq('body').on('click', '.wd-resolve-frm button', function () {
            jq('.wd-resolve-frm').find('button').removeAttr('clicked');
            jq(this).attr('clicked', true);
        });

        var ignore_showed = 0;
        jq('body').on('submit', '.wd-resolve-frm', function () {
            var that = jq(this);
            var is_modal = false;
            var parent = that.closest('tr');
            var btn = that.find("button[type=submit][clicked=true]");
            if (parent.is('tr') == false) {
                is_modal = true;
                parent = prev_trigger.closest('tr');
            }
            prev_trigger = btn;
            var data = jq(this).serialize();

            data += '&type=' + btn.data('type');
            var confirm_key = btn.data('confirm');
            if (btn.data('type') == 'ignore') {
                if (ignore_showed == 0) {
                    ignore_showed += 1;
                } else {
                    confirm_key = undefined;
                }
            }
            if (confirm_key != undefined) {
                var confirm_button = btn.data('confirm-button');
                jq.wd_confirm({
                    text: wd_scanning[confirm_key],
                    'confirmButton': wd_scanning[confirm_button],
                    'cancelButton': wd_scanning['cancel_confirm_btn'],
                    confirm: function (modal, e) {
                        call_ajax();
                        modal.removeClass('is-visible');
                    }
                });
            } else {
                call_ajax();
            }

            function call_ajax() {
                //we will need to check if this is load form action
                if (btn.data('type') == 'clean') {
                    var id = that.find('input[name="id"]').val();
                    var strings = wd_scanning['strings'];
                    jq.each(strings, function (i, v) {
                        if (i == id) {
                            jq('#wd-resolve-dialog').html(v);
                            jq('#wd-resolve-trigger').click();
                            return;
                        }
                    })
                } else {
                    jq.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data,
                        beforeSend: function () {
                            btn.attr('disabled', 'disabled').css({
                                'cursor': 'wait'
                            });
                            jq('.wd-error').addClass('wd-hide');
                        },
                        success: function (data) {
                            if (data.status == 1) {
                                switch (btn.data('type')) {
                                    case 'delete':
                                        if (is_modal) {
                                            WDP.closeOverlay();
                                        }
                                        location.reload();
                                        break;
                                    case 'clean':
                                        jq('#wd-resolve-dialog').html(data.result);
                                        jq('#wd-resolve-trigger').click();
                                        break;
                                    case 'resolve_ci':
                                        if (is_modal) {
                                            WDP.closeOverlay();
                                        }
                                        location.reload();
                                        break;
                                    case 'ignore':
                                        if (is_modal) {
                                            WDP.closeOverlay();
                                        }
                                        parent.fadeOut(500, function () {
                                            parent.remove();
                                            if (jq('#wd-ignore-list').is(':hidden')) {
                                                jq('#wd-ignore-list').removeClass('wd-hide');
                                            }
                                            var $element = jq(data.element);
                                            jq('#wd-ignore-list table tbody').append($element);
                                            $element.effect("highlight", {}, 1000);
                                            listening_to_issues_count();
                                            jQuery('body').trigger('after_an_issue_resolved', -1);
                                        });
                                        break;
                                    case 'undo':
                                        parent.fadeOut(500, function () {
                                            parent.remove();
                                            var $element = jq(data.element);
                                            jq('#wd-result-list table tbody').append($element);
                                            $element.effect("highlight", {}, 1000);
                                            if (jq('#wd-result-list table').is(':hidden')) {
                                                jq('#wd-result-list table').removeClass('wd-hide');
                                                jq('#wd-result-list .wd-success').addClass('wd-hide');
                                            }
                                            listening_to_issues_count();
                                            jQuery('body').trigger('after_an_issue_resolved', 1);
                                        });
                                        break;
                                }
                            } else {
                                if (is_modal) {
                                    WDP.closeOverlay();
                                }
                                jq('.wd-error').html(data.error).removeClass('wd-hide');
                            }
                            btn.removeAttr('disabled').css({
                                'cursor': 'pointer'
                            });
                        }
                    })
                }
            }

            function listening_to_issues_count() {
                var count = jq('#wd-result-list table tbody tr').size();
                if (count == 0) {
                    location.reload();
                } else {
                    jq('.wd-issues-count').text(count);
                }
            }

            function append_element() {

            }

            return false;
        })
    }

    resolve_action_submit();
    ajax_inline_update()
}

WDefender.scanning = function () {
    var jq = jQuery;

    function start_a_scan() {
        jq('#start_a_scan').submit(function () {
            var that = jq(this);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    that.find('button').attr('disabled', 'disabled').css({
                        'cursor': 'wait'
                    });
                },
                success: function (data) {
                    if (data.status == 1) {
                        location.href = data.redirect_url;
                    } else {
                        alert(data.error);
                    }
                }
            })
            return false;
        })
    }

    function refresh_scan_progress() {
        if (jq('#secret_key_scanning').size() > 0) {
            var files = [];
            var scanned_log = jq('.wd-scan-log');
            var trigger_log = 0;
            jq('#secret_key_scanning').submit(function () {
                var that = jq(this);
                jq.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: that.serialize(),
                    success: function (data) {
                        jq('.wd-progress span').css('width', data.progress + '%').text(data.progress + '%');
                        if (data.error == false) {
                            jq('.current_working').html(data.message);
                        }
                        if (data.complete == 1 || data.error == 1) {
                            location.href = data.url;
                        } else {
                            //merging the files
                            files = files.concat(data.scanned);
                            if (trigger_log == 0) {
                                queue_up_files(data.abs_path);
                                trigger_log = 1;
                            }
                            if (data.alert != 0 && jq('.wd-nested-wp').size() == 0) {
                                var html = jq('<div class="wd-caution wd-nested-wp" />');
                                html.html(data.alert);
                                jq('.wd-scan-main').prepend(html);
                            }
                            if (data.md5_alert != 0 && jq('.wd-md5-missing').size() == 0) {
                                var html = jq('<div class="wd-caution wd-md5-missing" />');
                                html.html(data.md5_alert);
                                jq('.wd-scan-main').prepend(html);
                            }
                            setTimeout(function () {
                                jq('#secret_key_scanning').submit();
                            }, 4000)
                        }
                    }
                })
                return false;
            });
            jq('#secret_key_scanning').submit();

            function queue_up_files(abs_path) {
                if (files.length > 0) {
                    //get the last out
                    var current = files.pop();
                    current = current.replace(abs_path, '/');
                    scanned_log.append(jq('<p/>').html(current));
                }
                setTimeout(function () {
                    queue_up_files(abs_path);
                }, 5);
            }
        }
    }

    function schedule_scan() {
        if (jq('#setup-scan-form').size() > 0) {
            jq('#setup-scan-form').submit(function () {
                var that = jq(this);
                jq.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: that.serialize(),
                    beforeSend: function () {
                        that.find('.update-notice').addClass('wd-hide');
                        that.find('button').attr('disabled', 'disabled').css({
                            'cursor': 'wait'
                        });
                    },
                    success: function (data) {
                        that.find('button').removeAttr('disabled').css('cursor', 'pointer');
                        location.reload();
                        /*if (data.status == 1) {
                         that.find('.update-notice.ok').removeClass('wd-hide').html(data.message);
                         } else {
                         that.find('.update-notice.err').removeClass('wd-hide').html(data.error);
                         }*/
                    }
                })
                return false;
            })
        }
    }

    function toggle_auto_scan() {
        jq('#toggle_auto_scan').change(function () {
            jq('#toggle_auto_scan_frm').submit();
        })
        jq('#toggle_auto_scan_frm').submit(function () {
            var that = jq(this);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                success: function (data) {
                    var div = that.closest('.setup-scan').find('.next-run-information');
                    var text = data.text;
                    if (text != null && text.length > 0) {
                        if (div.find('p').size() > 0) {
                            div.html(text);
                        } else {
                            div.append(text).fadeIn(500);
                        }
                    } else {
                        div.fadeOut(500, function () {
                            div.find('p').remove();
                        });
                    }
                    that.closest('h3').find('.toggle').first().attr('tooltip', data.tooltip);
                }
            })
            return false;
        });
    }

    function filter_by_type() {
        jq('#wd_filter_by_type').change(function () {
            var value = jq(this).val();
            if (value == 'all') {
                jq('#wd-scan-result-table tr').show();
            } else {
                jq('#wd-scan-result-table tbody tr[data-type!=' + value + ']').hide('500');
                jq('#wd-scan-result-table tbody tr[data-type=' + value + ']').show(500);
            }
        });
    }

    function ux() {
        jq('select[name="frequency"]').change(function () {
            if (jq(this).val() == 1) {
                jq('.day-container').hide();
            } else {
                jq('.day-container').show();
            }
        }).trigger('change');
    }

    function cancel_scan() {
        jq('#wd_cancel_scan').submit(function () {
            var that = jq(this);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    that.find('button').attr('disabled', 'disabled')
                },
                success: function (data) {
                    if (data.status == 1) {
                        location.href = data.url;
                    }
                }
            })
            return false;
        })
    }

    function toggle_scanning_log() {
        jq('.wd-scan-toggle-log').click(function () {
            var scan_log = jq('.wd-scan-log');
            var parent = scan_log.closest('.dev-box');
            if (scan_log.is(':visible')) {
                parent.addClass('wd-hide');
                jq(this).text(wd_scanning.show_log);
            } else {
                parent.removeClass('wd-hide');
                jq(this).text(wd_scanning.hide_log);
            }
        })
    }

    function close_on_click() {
        var clicked = false;
        jq('body').on('click', '.dev-overlay', function (e) {
            var toElement = jq(e.toElement);
            if (toElement.hasClass('box-scroll')) {
                if (clicked == false) {
                    WDP.closeOverlay();
                    clicked = true;
                    setTimeout(function () {
                        clicked = false;
                    }, 1000)
                }
            }
        });
        jq('body').on('click', '.wd-confirm-dialog', function (e) {
            var toElement = jq(e.toElement);
            if (toElement.hasClass('wd-confirm-dialog')) {
                jq('.wd-confirm-dialog').remove();
            }
        });
    }

    function init() {
        start_a_scan();
        refresh_scan_progress();
        schedule_scan();
        toggle_auto_scan();
        ux();
        filter_by_type();
        cancel_scan();
        close_on_click();
        toggle_scanning_log();
    }

    init();
}

WDefender.blackList = function () {
    var jq = jQuery;

    function formHandle() {
        jq('body').on('submit', '#activate_blacklist_frm', function () {
            var that = jq(this);
            var parent = jq('#wd-blacklist-widget');
            var overlay = WDefender.createOverlay(parent);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    that.find('button').attr('disabled', 'disabled');
                    parent.find('.wd-error').html('').hide();
                    overlay.appendTo(parent);
                },
                success: function (data) {
                    if (data.status == 1) {
                        var html = jq(data.html);
                        parent.replaceWith(html);
                        check_toggle_status();
                    } else {
                        overlay.remove();
                        that.find('button').removeAttr('disabled');
                        parent.find('.wd-error').html(data.error).show();
                    }
                }
            })
            return false;
        })
    }

    function toggleHandle() {
        jq('body').on('submit', '#toggle_blacklist_frm', function () {
            var that = jq(this);
            var parent = jq('#wd-blacklist-widget');
            var overlay = WDefender.createOverlay(parent);
            jq.ajax({
                type: 'POST',
                url: ajaxurl,
                data: that.serialize(),
                beforeSend: function () {
                    overlay.appendTo(parent);
                    parent.find('.wd-error').html('').hide();
                },
                success: function (data) {
                    if (data.status == 1) {
                        check_toggle_status();
                    } else {
                        overlay.remove();
                        parent.find('.wd-error').html(data.error).show();
                    }
                }
            })
            return false;
        });
        jq('body').on('change', '#toggle_blacklist', function () {
            jq('#toggle_blacklist_frm').submit();
        })
    }

    function check_toggle_status() {
        var parent = jq('#wd-blacklist-widget');

        if (parent.size() == 0) {
            return;
        }

        if (parent.find('.wd-progress').size() == 0 && parent.find('.wd-overlay').size() == 0) {
            //this mean everything ok
            return;
        }

        _check_toggle_status();

        function _check_toggle_status() {
            jq.get(location.href, function (html) {
                html = jq(html);
                var new_parent = html.find('#wd-blacklist-widget');
                if (new_parent.find('.wd-progress').size() == 0 && new_parent.find('.wd-overlay').size() == 0) {
                    parent.replaceWith(new_parent);
                } else {
                    setTimeout(_check_toggle_status, 5000);
                }
            })
        }
    }

    function init() {
        formHandle();
        toggleHandle();
        check_toggle_status();
    }

    init();
}

WDefender.dashboard = function () {
    var jq = jQuery;

    function install_snapshot() {
        if (jq('#wd_install_snapshot').size() > 0) {
            jq('#wd_install_snapshot').submit(function () {
                var that = jq(this);
                var parent = jq('#wd-backup-widget');
                var overlay = WDefender.createOverlay(parent);
                jq.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: that.serialize(),
                    beforeSend: function () {
                        overlay.appendTo(parent);
                        parent.find('.wd-error').html('').hide();
                    },
                    success: function (data) {
                        if (data.status == 1) {
                            location.reload();
                        } else {
                            overlay.remove();
                            parent.find('.wd-error').html(data.error).show();
                        }
                    }
                })
                return false;
            })
        }
    }

    function install_humming() {
        if (jq('#wd_install_humming').size() > 0) {
            jq('#wd_install_humming').submit(function () {
                var that = jq(this);
                var parent = jq('#wd-humming-widget');
                var overlay = WDefender.createOverlay(parent);
                jq.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: that.serialize(),
                    beforeSend: function () {
                        overlay.appendTo(parent);
                        parent.find('.wd-error').html('').hide();
                    },
                    success: function (data) {
                        if (data.status == 1) {
                            location.reload();
                        } else {
                            overlay.remove();
                            parent.find('.wd-error').html(data.error).show();
                        }
                    }
                })
                return false;
            })
        }
    }

    function init() {
        install_snapshot();
        install_humming();
    }

    init();
}

WDefender.createOverlay = function () {
    var jq = jQuery;
    var div = jq('<div class="wd-overlay"/>');
    div.html('<i class="wdv-icon wdv-icon-fw wdv-icon-refresh spin"></i>');
    return div;
};