define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    return function (config, element) {
        var panel = $(element),
            excerpt = panel.find('[data-role="comment-excerpt"]'),
            full = panel.find('[data-role="comment-full"]'),
            button = panel.find('[data-role="comment-toggle"]'),
            expanded = false;

        button.on('click', function () {
            expanded = !expanded;
            excerpt.prop('hidden', expanded);
            full.prop('hidden', !expanded);
            button.text(expanded ? $t('Collapse') : $t('Expand'))
                .attr('aria-expanded', expanded ? 'true' : 'false');
        });
    };
});
