function getDate(time) {
    time = time.match(/(\d+):(\d+):(\d+).(\d+)/);
    return new Date(0, 0, 1, parseInt(time[1], 10), parseInt(time[2], 10), parseInt(time[3], 10), parseInt(time[4], 10));
}
function getDuration(start, end) {
    start = getDate(start);
    end = getDate(end);
    return end.getTime() - start.getTime();
}
function reloadDuration() {
    var logPanelDetailedGrid = $('#log-panel-detailed-grid'),
        table = logPanelDetailedGrid.find('table'),
        prevDate = table.find('tbody tr').first().find('td:nth(1)').text(),
        nowDate,
        duration,
        totalDuration = 0,
        durationCell;
    if (table.find('.duration_total').length == 0) {
        table.find('thead tr:nth(0) th:nth(1)').after($('<th class="sort-numerical"><a data-curr-order="seq" style="cursor:pointer" class="duration-sort-btn">Duration</a></th>'));
        table.find('thead tr:nth(1) td:nth(1)').after($('<td class="duration_total" title="Total duration"></td>'));
        var sortByDurationBtn = table.find('.duration-sort-btn'),
            origOrder = table.find('thead a.asc, thead a.desc');
        if (origOrder) {
            sortByDurationBtn.data('orig-order', {elem: origOrder, dir: origOrder.hasClass('asc') ? 'asc' : 'desc'});
        }
        sortByDurationBtn.click(sortByDuration);
    }
    table.find('tbody tr').each(function() {
        durationCell = $(this).find('td.duration');
        if (durationCell.length == 0) {
            durationCell = $('<td class="duration"></td>');
            $(this).find('td:eq(1)').after(durationCell);
        }
        if (typeof durationCell.data('time') != typeof undefined) {
            nowDate = durationCell.data('time');
        }
        else {
            nowDate = $(this).find('td:eq(1)').text();
            durationCell.data('time', nowDate);
        }
        duration = getDuration(prevDate,nowDate);
        totalDuration += duration;
        durationCell.text(duration + ' ms');
        durationCell.data('duration', duration);
        prevDate = nowDate;
    });
    table.find('.duration_total').text(totalDuration + ' ms');
}
function sortByDuration(e) {
    e.preventDefault();
    var origOrder, newOrder;
    switch ($(e.target).data('curr-order')) {
        case 'seq':
            newOrder = 'asc';
            origOrder = $(e.target).data('orig-order');
            if (origOrder) {
                origOrder.elem.removeClass(origOrder.dir);
            }
            $(e.target).addClass('asc');
            break;
        case 'asc':
            newOrder = 'desc';
            $(e.target).removeClass('asc').addClass('desc');
            break;
        case 'desc':
            newOrder = 'seq';
            origOrder = $(e.target).data('orig-order');
            if (origOrder) {
                origOrder.elem.addClass(origOrder.dir);
            }
            $(e.target).removeClass('desc');
            break;
    }
    var logPanelDetailedGrid = $('#log-panel-detailed-grid'),
        tbody = logPanelDetailedGrid.find('table tbody'),
        tda,
        tdb,
        tdas,
        tdbs;
    tbody.find('tr').sort(function(a,b) {
        tda = parseInt($(a).find('td.duration').data('duration'), 10);
        tdb = parseInt($(b).find('td.duration').data('duration'), 10);
        switch (newOrder) {
            case 'seq':
                tdas = parseInt($(a).find('td:eq(0)').text(), 10);
                tdbs = parseInt($(b).find('td:eq(0)').text(), 10);
                return tdas > tdbs ? 1 : tdas < tdbs ? -1 : 0;
                break;
            case 'asc':
                return tda > tdb ? 1 : tda < tdb ? -1 : 0;
                break;
            case 'desc':
                return tda < tdb ? 1 : tda > tdb ? -1 : 0;
                break;
        }
    }).appendTo(tbody);
    $(e.target).data('curr-order', newOrder);
    console.log('New order: ' + newOrder);
}
(function () {
    var logPanelDetailedGrid = $('#log-panel-detailed-grid');
    if (logPanelDetailedGrid.length > 0) {
        var btn = $('<a style="cursor:pointer" class="log-panel-show-duration-btn">Show duration</a>');
        logPanelDetailedGrid.find('.summary')
            .append(' ')
            .append(btn);
        btn.click(function() {
            btn.remove();
            reloadDuration();
        });
    }
})();