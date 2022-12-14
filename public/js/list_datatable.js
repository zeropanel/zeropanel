
// Class definition
var KTDatatablesOrderSide = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function () {
        dt = $("#zero_order_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[3, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/order",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'order_total' },
                { data: 'order_status' },
                { data: 'order_type' },
                { data: 'created_time' },
                { data: 'no' },
                { data: null},
            ],
            
            columnDefs: [
                {
                    targets: -1,
                    orderable: false,
                    className: 'text-end',
                    render: function (data) {
                        return `<a class="btn btn-sm btn-light-primary" href="/user/order/${data.no}" >详情</a>`;
                    },
                },
                {
                    targets: 1,
                    render: function (data, type, row) {
                        if (data == 'paid') {
                            return '<div class="badge font-weight-bold badge-light-success fs-6">支付成功</div>';
                        } else if (data == 'pending') {
                            return `<div class="badge font-weight-bold badge-light-warning fs-6">等待支付</div>`;
                        } else if (data == 'invalid') {
                            return `<div class="badge font-weight-bold badge-light-danger fs-6">订单失效</div>`;
                        }
                    },
                },
                
            ],
            
        });

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesOrderSide.init();
});

// ticket table
var KTDatatablesTicketSide = function () {
    // Shared variables
    var table;
    var dt;

    // Private functions
    var initDatatable = function () {
        dt = $("#zero_ticket_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[2, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/ticket",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'id' },
                { data: 'title' },
                { data: 'status' },
                { data: 'datetime' },
                { data: null},
            ],
            columnDefs: [
                {
                    targets: 2,
                    render: function (data) {
                        if (data == 1) {
                            return '<div class="badge font-weight-bold badge-light-success fs-6">活跃</div>';
                        } else {
                            return '<div class="badge font-weight-bold badge-light fs-6">关闭</div>';
                        }
                    },
                },
                {
                    targets: -1,
                    render: function (data) {
                        return `<a class="btn btn-sm btn-light-primary" href="/user/ticket/${data.id}/view" >详情</a>`;
                    },
                },
                
            ],
            
        });

        table = dt.$;

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesTicketSide.init();
});

//登录记录
var KTDatatablesSigninLogSide = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#zero_signin_log_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[2, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/loginlog",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'ip' },
                { data: 'location' },
                { data: 'datetime' },
            ],
        });

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesSigninLogSide.init();
});

// used log
var KTDatatablesUsedLogSide = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#zero_used_log_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[2, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/uselog",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'ip' },
                { data: 'location' },
                { data: 'datetime' },
            ],
        });

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesUsedLogSide.init();
});

// sub log
var KTDatatablesSubscribeLogSide = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#zero_subscribe_log_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[4, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/sublog",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'subscribe_type' },
                { data: 'request_ip' },
                { data: 'location'},
                { data: 'request_user_agent'},
                { data: 'request_time' },
            ],
            columnDefs: [
                {
                    targets: 2,
                    orderable: false,
                },
                {
                    targets: 3,
                    orderable: false
                }
                
                
            ],
        });

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesSubscribeLogSide.init();
});

// traffic log
var KTDatatablesTrafficLogSide = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#zero_traffic_log_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[2, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/trafficlog",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'node_name' },
                { data: 'traffic' },
                { data: 'datetime' },
            ],
        });

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesTrafficLogSide.init();
});

// commission
var KTDatatablesUserGetCommissionLogSide = function () {
    var table;
    var dt;

    var initDatatable = function () {
        dt = $("#zero_user_get_commission_log_table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[1, 'desc']],
            stateSave: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            ajax: {
                url: "/user/ajax_data/table/get_commission_log",
            },
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.12.1/i18n/zh.json",
            },
            columns: [
                { data: 'ref_get' },
                { data: 'datetime'},
            ],
        });

    }
    return {
        init: function () {
            initDatatable();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesUserGetCommissionLogSide.init();
});