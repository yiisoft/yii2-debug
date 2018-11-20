(function () {
    'use strict';

    var on = function (element, event, handler) {
        if (element instanceof NodeList) {
            element.forEach(function (value) {
                value.addEventListener(event, handler, false);
            });
            return;
        }
        if (!(element instanceof Array)) {
            element = [element];
        }
        for (var i in element) {
            if (typeof element[i].addEventListener !== 'function') {
                console.log(element[i]);
                continue;
            }
            element[i].addEventListener(event, handler, false);
        }
    }, off = function (element, event, handler) {
        if (element instanceof NodeList) {
            element.forEach(function (value) {
                value.removeEventListener(event, handler, false);
            });
            return;
        }
        if (!(element instanceof Array)) {
            element = [element];
        }
        for (var i in element) {
            if (typeof element[i].removeEventListener !== 'function') {
                console.log(element[i]);
                continue;
            }
            element[i].removeEventListener(event, handler, false);
        }
    };

    var Timeline = function (options) {
        this.options = options;
        var self = this;
        this.init = function () {
            if (this.options.$focus) {
                this.options.$focus.focus();
                delete this.options.$focus;
            }
            var links = document.querySelectorAll('.debug-timeline-panel__item a');
            links.forEach(function (link) {
                new Tooltip(link);

                on(link, 'show.bs.tooltip', function() {
                    if (this.dataset.hasDataAttr('memory')) {
                        var data = this.dataset.getDataAttr('memory');
                        self.options.$memory.textContent = data[0];
                        self.options.$memory.style.bottom = data[1] + '%';
                    }
                });
            });
            return self;
        };
        this.setFocus = function ($elem) {
            this.options.$focus = $elem;
            return $elem;
        };

        on(document, 'pjax:success', function () {
            self.init();
        });

        on(self.options.$header, 'dblclick', function () {
            self.options.$timeline.classList.toggle('inline');
        });
        on(self.options.$header, 'click', function (e) {
            if (e.target.tagName.toLowerCase() === 'button') {
                self.options.$timeline.classList.toggle('inline');
            }
        });

        on(self.options.$search, 'change', function () {
            self.setFocus(this);
            this.form.submit();
        });

        this.init();
    };

    (new Timeline({
        '$timeline': document.querySelector('.debug-timeline-panel'),
        '$header': document.querySelector('.debug-timeline-panel__header'),
        '$search': document.querySelectorAll('.debug-timeline-panel__search input'),
        '$memory': document.querySelector('.debug-timeline-panel__memory .scale')
    }));
})();
