(function($) {
  'use strict';
  var typingTimer;
  var doingSearch;
  var last_keyword;

  function admin_live_search_sort() {
    jQuery('table.wp-list-table').find('th.sortable').each(function() {
      jQuery(this).addClass('admin_live_search_sortable');
      var columnid = jQuery(this).attr('id');
      var sortelement = jQuery(this).find('a:first');
      var href = jQuery(sortelement).attr('href');
      jQuery(sortelement).addClass('admin_live_search_sort').attr('href', 'javascript:;');
      if (href.match(/orderby=([a-zA-Z0-9_-]+)/)) {
        jQuery(sortelement).attr('data-orderby', href.match(/orderby=([a-zA-Z0-9_-]+)/)[1]);
        jQuery(sortelement).attr('data-order', href.match(/order=(asc|desc|ASC|DESC)/)[1]);
        jQuery(sortelement).attr('data-orderid', columnid);
        var current_sorted_column = jQuery('.admin_live_search_cache:first').attr('data-orderid');
        var current_sorted_dir = jQuery('.admin_live_search_cache:first').attr('data-order');
        if (columnid == current_sorted_column) {
          jQuery(this).removeClass('sorteable').addClass('sorted');
          jQuery(this).removeClass('asc').removeClass('desc').addClass(current_sorted_dir);
          if (current_sorted_dir == 'asc') {
            jQuery(sortelement).attr('data-order', 'desc')
          } else {
            jQuery(sortelement).attr('data-order', 'asc')
          }
        }
        jQuery(sortelement).click(function() {
          jQuery('.admin_live_search_cache:first').attr('data-orderby', jQuery(this).attr('data-orderby'));
          jQuery('.admin_live_search_cache:first').attr('data-order', jQuery(this).attr('data-order'));
          jQuery('.admin_live_search_cache:first').attr('data-orderid', jQuery(this).attr('data-orderid'));
          dosearch()
        })
      }
    })
  }

  function admin_live_search_pagination() {
    jQuery('.pagination-links a.next-page').attr('href', 'javascript:;');
    jQuery('.pagination-links a.prev-page').attr('href', 'javascript:;');
    jQuery('.pagination-links a.first-page').attr('href', 'javascript:;');
    jQuery('.pagination-links a.last-page').attr('href', 'javascript:;');
    var paged = parseInt(jQuery('input[name="paged"]:first').val());
    var max_paged = parseInt(jQuery('.total-pages:first').text());
    if (jQuery('.pagination-links a.next-page').length) {
      jQuery('.pagination-links a.next-page').click(function() {
        jQuery('input[name="paged"]').val(paged + 1);
        dosearch()
      })
    }
    if (jQuery('.pagination-links a.prev-page').length) {
      jQuery('.pagination-links a.prev-page').click(function() {
        jQuery('input[name="paged"]').val(paged - 1);
        dosearch()
      })
    }
    if (jQuery('.pagination-links a.first-page').length) {
      jQuery('.pagination-links a.first-page').click(function() {
        jQuery('input[name="paged"]').val(1);
        dosearch()
      })
    }
    if (jQuery('.pagination-links a.last-page').length) {
      jQuery('.pagination-links a.last-page').click(function() {
        jQuery('input[name="paged"]').val(max_paged);
        dosearch()
      })
    }
  }

  function dosearch() {
    if (doingSearch) return;
    var keyword = jQuery('.admin_live_search_keyword').val();
    doingSearch = !0;
    last_keyword = keyword;
    var data_fields = jQuery('#posts-filter').find('input,select,checkbox').serialize();
    var order_by = jQuery('.admin_live_search_cache:first').attr('data-orderby');
    var order = jQuery('.admin_live_search_cache:first').attr('data-order');
    var orderid = jQuery('.admin_live_search_cache:first').attr('data-orderid');
    var admin_live_search_filter = jQuery('#admin_live_search_filter option:selected').val();
    jQuery('#admin_live_search_filter').attr('disabled', !0);
    var data = {
      'action': 'admin_live_search',
      'url': document.location.href,
      'fields': data_fields,
      'keyword': keyword,
      'orderby': order_by,
      'order': order,
      'pagenow': pagenow,
      'admin_live_search_filter': admin_live_search_filter
    };
    jQuery('.admin_live_search_sp').addClass('is-active');
    jQuery.get(ajaxurl, data, function(response) {
      jQuery('.admin_live_search_cache').html(response.data);
      jQuery('table.wp-list-table').html(jQuery('.admin_live_search_cache').find('table.wp-list-table').html());
      if (jQuery('.admin_live_search_cache').find('.admin_live_search_rt').length == 0) {
        jQuery('.admin_live_search_rt').html('')
      }
      jQuery('.admin_live_search_rt').html(jQuery('.admin_live_search_cache').find('.admin_live_search_rt').html());
      jQuery('ul.subsubsub').html(jQuery('.admin_live_search_cache').find('ul.subsubsub').html());
      jQuery('div.tablenav-pages').html(jQuery('.admin_live_search_cache').find('div.tablenav-pages').html());
      if (jQuery('.admin_live_search_cache').find('.admin_live_search_message').length) {
        jQuery('.admin_live_search_message_wrap').html(jQuery('.admin_live_search_cache').find('.admin_live_search_message').html());
        jQuery('.admin_live_search_message_wrap').css('visibility', 'visible')
      }
      jQuery('.admin_live_search_cache').html('');
      admin_live_search_sort();
      admin_live_search_pagination();
      jQuery('.admin_live_search_sp').removeClass('is-active');
      jQuery('#admin_live_search_filter').attr('disabled', !1);
      doingSearch = !1
    })
  }
  jQuery(document).ready(function() {
    if (typeof pagenow == "undefined") {
      var pagenow = ''
    }
    if (typeof typenow == "undefined") {
      var typenow = 'post'
    }
    if (jQuery('#post-search-input[name="s"]').length && admin_live_search_show) {
      jQuery('<span class="subtitle admin_live_search_rt"></span>').insertAfter(jQuery('h1.wp-heading-inline').parent().find('a:first'));
      jQuery('<span class="spinner admin_live_search_sp"></span>').insertBefore(jQuery('#post-search-input[name="s"]'));
      jQuery('<div class="admin_live_search_cache" data-sortby="" data-sort="" data-orderid="" style="display:none"></div>').insertBefore(jQuery('#post-search-input[name="s"]'));
      jQuery('#post-search-input[name="s"]').addClass('admin_live_search_keyword');
      jQuery('.admin_live_search_keyword').on('change keyup copy paste cut reset click', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function() {
          jQuery('.admin_live_search_cache:first').attr('data-paged', 1);
          dosearch()
        }, 620)
      });
      var text = jQuery('#search-submit').attr('type', 'button').hide().attr('disabled', !0).val();
      jQuery('#post-search-input[name="s"]').attr('placeholder', text);
      jQuery('#admin_live_search_filter').insertBefore('#post-search-input[name="s"]').show();
      jQuery('#admin_live_search_filter').change(function() {
        if (jQuery('#post-search-input[name="s"]').val() != '') {
          dosearch()
        }
      });
      jQuery('#posts-filter').on('submit', function() {
        if (jQuery('#bulk-edit:visible').length == 0  &&  jQuery('#bulk-action-selector-top').val() == -1) {
          dosearch();
          return !1
        }
      });
      admin_live_search_sort();
      admin_live_search_pagination();
      jQuery('.admin_live_search_keyword').focus()
    }
    if (jQuery('#admin_live_search_cpt_status_chkbox').length > 0) {
      jQuery('#admin_live_search_cpt_status_chkbox').click(function() {
        jQuery('.admin_live_search_sp').addClass('is-active');
        var data = {
          'action': 'admin_live_search_set_cpt_status',
          'url': document.location.href,
          'admin_live_search_cpt_status_chkbox': jQuery('#admin_live_search_cpt_status_chkbox:checked').length,
        };
        jQuery.get(ajaxurl, data, function(response) {
          jQuery('.admin_live_search_sp').removeClass('is-active')
        })
      })
    }
  })
})(jQuery)
