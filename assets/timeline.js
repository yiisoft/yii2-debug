(function () {
    'use strict';

    /**
     * @param {jQuery} open popover
     */
    var _popoverOpen;

    var Timeline = function (options) {

        this.options = options;
        var self = this;

        this.init = function () {
            if (this.options.$focus !== undefined) {
                this.options.$focus.focus();
                delete this.options.$focus;
            }

            self.options.$timeline.find('[data-toggle="popover"]')
                .on('show.bs.popover', function () {
                    var $this = $(this);
                    if (_popoverOpen && _popoverOpen.data('i') != $this.data('i')) {
                        _popoverOpen.popover('hide');
                    }
                    _popoverOpen = $this;
                })
                .popover();
            self.hidePopover();
            return self;
        };

        this.hidePopover = function () {
            if (_popoverOpen) {
                _popoverOpen.popover('hide');
                _popoverOpen = null;
            }
        }
        this.setFocus = function ($elem) {
            this.options.$focus = $elem;
            return $elem;
        };

        $(document).on('pjax:success', function () {
            self.init()
        });
        self.options.$inline.on('dblclick', function () {
            self.options.$timeline.toggleClass('inline');
            self.hidePopover();
        })
        self.options.$search.on('change', function () {
            self.setFocus($(this)).submit();
        })
        self.options.$timeline.affix({
            offset: {
                top: function () {
                    return (this.top = self.options.$timeline.find('b:first').offset().top)
                }
            }
        });
        this.init();
    };

    (new Timeline({
        '$timeline': $('.debug-timeline-panel'),
        '$inline': $('.debug-timeline-panel__header'),
        '$search': $('.debug-timeline-panel__search input')
    }));
})();