/**
 * Dashboard Plugin - Admin JavaScript
 * 
 * @since 1.1.3
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Make log entries expandable
        $('.log-entry').each(function() {
            var $entry = $(this);
            if ($entry.text().length > 100) {
                var fullText = $entry.text();
                var shortText = fullText.substring(0, 100) + '...';
                $entry.html(shortText);
                $entry.append('<a href="#" class="expand-log">Show more</a>');
                $entry.data('fullText', fullText);
                $entry.data('shortText', shortText);
                $entry.data('expanded', false);
            }
        });
        
        // Handle expand/collapse of log entries
        $(document).on('click', '.expand-log', function(e) {
            e.preventDefault();
            
            var $entry = $(this).closest('.log-entry');
            var isExpanded = $entry.data('expanded');
            
            if (isExpanded) {
                $entry.html($entry.data('shortText'));
                $entry.append('<a href="#" class="expand-log">Show more</a>');
                $entry.data('expanded', false);
            } else {
                $entry.html($entry.data('fullText'));
                $entry.append('<a href="#" class="expand-log">Show less</a>');
                $entry.data('expanded', true);
            }
        });
        
        // Toggle redirect URL field based on checkbox
        $('#dashboard_redirect_after_login').on('change', function() {
            var $redirectTarget = $('#dashboard_redirect_target').closest('tr');
            
            if ($(this).is(':checked')) {
                $redirectTarget.show();
            } else {
                $redirectTarget.hide();
            }
        }).trigger('change');
    });
    
})(jQuery); 