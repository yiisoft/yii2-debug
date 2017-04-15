(function () {

    /* Converts time "HH:MM:SS.MS" to Date object */
    function getDate(time) {
        time = time.match(/(\d+):(\d+):(\d+).(\d+)/);
        return new Date(
            0,
            0,
            1,
            parseInt(time[1], 10),
            parseInt(time[2], 10),
            parseInt(time[3], 10),
            parseInt(time[4], 10)
        );
    }

    /* Returns duration between two times in "HH:MM:SS.MS" format */
    function getDuration(start, end) {
        start = getDate(start);
        end = getDate(end);
        return end.getTime() - start.getTime();
    }

    /* Reloads duration column */
    function reloadDuration() {
        var logPanelDetailedGrid = $('#log-panel-detailed-grid'),
            table = logPanelDetailedGrid.find('table'),
            prevDate = table.find('tbody tr').first().find('td:eq(1)').text(),
            nowDate,
            duration,
            totalDuration = 0,
            durationCell;

        /* If header was not created yet, create one */
        if (table.find('.duration_total').length == 0) {

            /* Adds header cell for duration column */
            table.find('thead tr:eq(0) th:eq(1)')
                .after(
                    $(
                        '<th class="sort-numerical">' +
                            '<a data-curr-order="seq" style="cursor:pointer" class="duration-sort-btn">Duration</a>' +
                        '</th>'
                    )
                );

            /* Adds "total duration" cell in filters row */
            table.find('thead tr:eq(1) td:eq(1)')
                .after($('<td class="duration_total" title="Total duration"></td>'));

            /* Stores original order (user selected) for display purposes */
            var sortByDurationBtn = table.find('.duration-sort-btn'),
                origOrder = table.find('thead a.asc, thead a.desc');
            if (origOrder) {
                sortByDurationBtn.data('orig-order', {elem: origOrder, dir: origOrder.hasClass('asc') ? 'asc' : 'desc'});
            }

            /* Attaches click event handler for sort button */
            sortByDurationBtn.click(sortByDuration);
        }

        /* Add duration cell to each row */
        table.find('tbody tr').each(function() {
            durationCell = $(this).find('td.duration');

            /* If duration cell not yet exists, create one */
            if (durationCell.length == 0) {
                durationCell = $('<td class="duration"></td>');
                $(this).find('td:eq(1)').after(durationCell);
                nowDate = $(this).find('td:eq(1)').text();
                durationCell.data('time', nowDate);
            }
            else {
                nowDate = durationCell.data('time');
            }

            /* Calculates duration */
            duration = getDuration(prevDate,nowDate);

            /* Increments total duration counter */
            totalDuration += duration;

            /* Displays duration */
            durationCell.text(duration + ' ms');

            /* Stores duration in data */
            durationCell.data('duration', duration);

            /* Stores date for next row calculation */
            prevDate = nowDate;
        });
        table.find('.duration_total').text(totalDuration + ' ms');
    }

    /* Sorts rows by duration, depending */
    function sortByDuration(e) {
        e.preventDefault();
        var origOrder, newOrder;

        /* Cycles ordering depending on current ordering mode */
        switch ($(e.target).data('curr-order')) {
            case 'seq':
                newOrder = 'asc';

                /* If original ordering exists, remove it */
                origOrder = $(e.target).data('orig-order');
                if (origOrder) {
                    origOrder.elem.removeClass(origOrder.dir);
                }

                /* Add new ordering class to sorting button */
                $(e.target).addClass('asc');
                break;
            case 'asc':
                newOrder = 'desc';

                /* Remove existing ordering and add new ordering class to sorting button */
                $(e.target).removeClass('asc').addClass('desc');
                break;
            case 'desc':
                newOrder = 'seq';

                /* If original ordering exists, switch back to it */
                origOrder = $(e.target).data('orig-order');
                if (origOrder) {
                    origOrder.elem.addClass(origOrder.dir);
                }

                /* Add new ordering class to sorting button */
                $(e.target).removeClass('desc');
                break;
        }
        var logPanelDetailedGrid = $('#log-panel-detailed-grid'),
            tbody = logPanelDetailedGrid.find('table tbody'),
            tda,
            tdb,
            tdas,
            tdbs;

        /* Do row sorting */
        tbody.find('tr').sort(function(a,b) {

            /* Prepare duration values */
            tda = $(a).find('td.duration').data('duration');
            tdb = $(b).find('td.duration').data('duration');
            switch (newOrder) {
                case 'seq':

                    /* Prepare sequence number values */
                    tdas = parseInt($(a).find('td:eq(0)').text(), 10);
                    tdbs = parseInt($(b).find('td:eq(0)').text(), 10);

                    /* Order ascending, by sequence number values */
                    return tdas > tdbs ? 1 : tdas < tdbs ? -1 : 0;
                    break;
                case 'asc':

                    /* Order ascending, by duration values */
                    return tda > tdb ? 1 : tda < tdb ? -1 : 0;
                    break;
                case 'desc':

                    /* Order descending, by duration values */
                    return tda < tdb ? 1 : tda > tdb ? -1 : 0;
                    break;
            }
        }).appendTo(tbody);

        /* Store new order */
        $(e.target).data('curr-order', newOrder);
    }

    var logPanelDetailedGrid = $('#log-panel-detailed-grid');
    if (logPanelDetailedGrid.length > 0) {

        /* Create "Show duration" button */
        var btn = $('<a style="cursor:pointer" class="log-panel-show-duration-btn">Show duration</a>');
        logPanelDetailedGrid.find('.summary')
            .append(' ')
            .append(btn);

        /* Catch "Show duration" click events */
        btn.click(function() {

            /* Remove button (because we can't reverse it) */
            btn.remove();

            /* Reload duration column */
            reloadDuration();
        });
    }
})();