/**
 * Focal Point plugin for Craft CMS
 *
 * FocalPointField Field JS
 *
 * @author    Værsågod
 * @copyright Copyright (c) 2018 Værsågod
 * @link      www.vaersaagod.no
 * @package   FocalPoint
 * @since     1.0.0FocalPointFocalPointField
 */

 ;(function ( $, window, document, undefined ) {

    var craftPositonTranslate = {
        'top-left': '0% 0%',
        'top-center': '50% 0%',
        'top-right': '100% 0%',
        'center-left': '0% 50%',
        'center-center': '50% 50%',
        'center-right': '100% 50%',
        'bottom-left': '0% 100%',
        'bottom-center': '50% 100%',
        'bottom-right': '100% 100%'
    };

    // Plugin constructor
    function Plugin( element ) {
        $(function () {
            var $field = $(element);
            var $wrapper = $field.find('[data-focalpointfield]');
            var $image = $field.find('[data-focalpointfield-image]');
            var $input = $field.find('[data-focalpointfield-value]');
            var $marker = $('<div data-focalpointfield-marker></div>');
            var isDragging = false;

            function placeMarker(x, y) {
                var width = $wrapper.outerWidth();
                var height = $wrapper.outerHeight();
                $marker.css({ top: Math.round((y/100)*height), left: Math.round((x/100)*width) });
            }

            function parseValue(val) {
                $wrapper.append($marker);
                if (craftPositonTranslate[val] !== undefined) {
                    val = craftPositonTranslate[val];
                }

                var arr = val.split(' ');

                if (arr.length === 2) {
                    var x = Math.max(0, Math.min(Number(arr[0].replace('%', '')), 100));
                    var y = Math.max(0, Math.min(Number(arr[1].replace('%', '')), 100));
                    placeMarker(x, y);
                }
            }

            function setValue(x, y) {
                $input.val(x + '% ' + y + '%');
                placeMarker(x, y);
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
                    setTimeout(function(){
                        parseValue($input.val() || '50% 50%');
                    }, 100);
                });
            }
        });
    }

    var pluginName = 'FocalPointFocalPointField';

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
