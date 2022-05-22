/**
 * Focal Point Field plugin for Craft CMS 4.x
 *
 * @link      https://www.vaersaagod.no
 * @copyright Copyright (c) 2022 Værsågod
 */

;(function ( $, window, document, undefined ) {

    var defaultValue = {
        x: 50,
        y: 50,
        css: '50% 50%',
    };

    // Plugin constructor
    function Plugin( element ) {
        $(function () {
            var $field = $(element);
            var $wrapper = $field.find('.focalpointfield-wrapper');
            var $image = $field.find('.focalpointfield-thumb');
            var $input = $field.find('[data-focalpointfield-value]');
            var $marker = $('<div class="focalpointfield-marker" />');
            var isDragging = false;
            var currentValue = null;

            function placeMarker(x, y) {
                var width = $wrapper.outerWidth();
                var height = $wrapper.outerHeight();
                $marker.css({ left: x + '%', top: y + '%' });
            }

            function parseValue(val) {
                $wrapper.append($marker);
                try {
                    currentValue = JSON.parse($input.val());
                } catch(e) {
                    currentValue = null;
                }
                currentValue = currentValue || defaultValue;
                placeMarker(currentValue.x, currentValue.y);
            }

            function setValue(x, y) {
                placeMarker(x, y);
                $input.val(JSON.stringify({
                    x: x,
                    y: y,
                    css: x + '% ' + y + '%'
                }));
            }

            function parsePosition(pageX, pageY) {
                var precision = Math.pow(10, 2);
                var parentOffset = $wrapper.offset();
                var imageWidth = $wrapper.outerWidth();
                var imageHeight = $wrapper.outerHeight();
                var posX = pageX - parentOffset.left;
                var posY = pageY - parentOffset.top;
                var percentX = Math.round((posX/imageWidth)*100*precision) / precision;
                var percentY = Math.round((posY/imageHeight)*100*precision) / precision;

                percentX = Math.max(0, Math.min(percentX, 100));
                percentY = Math.max(0, Math.min(percentY, 100));

                setValue(percentX, percentY);
            }

            $wrapper.on('click', function (e) {
                parsePosition(e.pageX, e.pageY);
            });

            $marker.on('mousedown', function (e) {
                isDragging = true;
            });

            $marker.on('mouseup', function (e) {
                isDragging = false;
            });

            $wrapper.on('mouseleave', function (e) {
                isDragging = false;
            });

            $(window).on('mousemove', function (e) {
                if (isDragging) {
                    parsePosition(e.pageX, e.pageY);
                }
            });

            if ($wrapper.length > 0) {
                $image.waitForImages().done(function(){
                    setTimeout(parseValue, 100);
                });
            }
        });
    }

    var pluginName = 'FocalPointField';

    $.fn.waitForImages = function () {
        var def = $.Deferred();
        var count = this.length;
        this.each(function () {
            if (this.complete) {
                if (!--count) {
                    def.resolve();
                }
            } else {
                $(this).on('load', function () {
                    if (!--count) {
                        def.resolve();
                    }
                });
            }
        });
        return def.promise();
    };

    $.fn[pluginName] = function ( options ) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName,
                    new Plugin( this, options ));
            }
        });
    };

})( jQuery, window, document );
