//"use strict";
//clipboard
var clipboard = new ClipboardJS('.copy-text');
clipboard.on('success', function(e) {
    getResult("ε€εΆζε", "", "success");
});

// get result 
function getResult(titles, texts, icons) {
    Swal.fire({
        title: titles,
        text: texts,
        icon: icons,
        buttonsStyling: false,
        confirmButtonText: "OK",
        customClass: {
            confirmButton: "btn btn-primary"
        }
    });
}

//get load
function getLoad() {
    Swal.fire({
        title: '',
        text: '',
        timer: 50000,
        confirmButtonText: "",
        didOpen: function() {
            Swal.showLoading()
        }
    }).then(function(result){
        if (result.dismiss == "timer") {
            console.log("I was closed by the timer")
        }
    });
}

// Class definition
var KTUsersUpdateName = function () {
    // Shared variables
    const element = document.getElementById('zero_modal_user_update_name');
    const form = element.querySelector('#zero_modal_user_update_name_form');
    const modal = new bootstrap.Modal(element);

    // Init add schedule modal
    var initUpdateName = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'profile_name': {
                        validators: {
                            notEmpty: {
                                message: 'Naem is required'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-users-modal-action="submit"]');
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // Simulate form submission. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        setTimeout(function () {
                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;

                            // Show popup confirmation
                            $.ajax({
                                type: "POST",
                                url: "/user/update_name",
                                dataType: "json",
                                data: {
                                    newusername: $("#profile_name").val()
                                },
                                success: function(data) {
                                    if (data.ret == 1) {
                                        Swal.fire({
                                            text: data.msg,
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        }).then(function (result) {
                                            if (result.isConfirmed) {
                                                modal.hide();
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        getResult(data.msg, '', 'error');
                                    }
                                }
                            });
                            //form.submit(); // Submit form
                        }, 2000);
                    }
                });
            }
        });
    }

    return {
        // Public functions
        init: function () {
            initUpdateName();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUsersUpdateName.init();
});

// update email
var KTUsersUpdateEmail = function () {
    // Shared variables
    const element = document.getElementById('zero_modal_user_update_email');
    const form = element.querySelector('#zero_modal_user_update_email_form');
    const modal = new bootstrap.Modal(element);

    // Init add schedule modal
    var initUpdateEmail = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'profile_email': {
                        validators: {
                            notEmpty: {
                                message: 'Email address is required'
                            },
                            emailAddress: {
                                message: 'ι?η?±ζ ΌεΌδΈζ­£η‘?'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-users-modal-action="submit"]');
        submitButton.addEventListener('click', function (e) {
            // Prevent default button action
            e.preventDefault();

            // Validate form before submit
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        // Show loading indication
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        // Disable button to avoid multiple click 
                        submitButton.disabled = true;

                        // Simulate form submission. For more info check the plugin's official documentation: https://sweetalert2.github.io/
                        setTimeout(function () {
                            // Remove loading indication
                            submitButton.removeAttribute('data-kt-indicator');

                            // Enable button
                            submitButton.disabled = false;

                            // Show popup confirmation 
                            $.ajax({
                                type: "POST",
                                url: "/user/update_email",
                                dataType: "json",
                                data: {
                                    newemail: $("#profile_email").val()
                                },
                                success: function(data) {
                                    if(data.ret === 1) {
                                        Swal.fire({
                                            text: data.msg,
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        }).then(function (result) {
                                            if (result.isConfirmed) {
                                                modal.hide();
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        getResult(data.msg, '', 'error');
                                    }
                                }
                            });
                        }, 2000);
                    }
                });
            }
        });
    }

    return {
        // Public functions
        init: function () {
            initUpdateEmail();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUsersUpdateEmail.init();
});

// update password
var KTUsersUpdatePassword = function () {
    // Shared variables
    const element = document.getElementById('zero_modal_user_update_password');
    const form = element.querySelector('#zero_modal_user_update_password_form');
    const modal = new bootstrap.Modal(element);

    // Init add schedule modal
    var initUpdatePassword = () => {

        // Init form validation rules. For more info check the FormValidation plugin's official documentation:https://formvalidation.io/
        var validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'current_password': {
                        validators: {
                            notEmpty: {
                                message: 'θ―·θΎε₯ε½εε―η '
                            }
                        }
                    },
                    'password': {
                        validators: {
                            notEmpty: {
                                message: 'θ―·θΎε₯ζ°ε―η '
                            },
                            callback: {
                                message: 'θ―·θΎε₯ζζηε―η ',
                                callback: function (input) {
                                    if (input.value.length > 0) {
                                        return validatePassword();
                                    }
                                }
                            }
                        }
                    },
                    'confirm_password': {
                        validators: {
                            notEmpty: {
                                message: 'θ―·η‘?θ?€ζ°ε―η '
                            },
                            identical: {
                                compare: function () {
                                    return form.querySelector('[name="new_password"]').value;
                                },
                                message: 'ζ°ε―η δΈ€ζ¬‘θΎε₯δΈδΈθ΄'
                            }
                        }
                    },
                },

                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row',
                        eleInvalidClass: '',
                        eleValidClass: ''
                    })
                }
            }
        );

        // Submit button handler
        const submitButton = element.querySelector('[data-kt-users-modal-action="submit"]');
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();
            if (validator) {
                validator.validate().then(function (status) {
                    console.log('validated!');

                    if (status == 'Valid') {
                        submitButton.setAttribute('data-kt-indicator', 'on');

                        submitButton.disabled = true;

                        setTimeout(function () {
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                            $.ajax({
                                type: "POST",
                                url: "/user/update_password",
                                dataType: "json",
                                data: {
                                    current_password: $("#current_password").val(),
                                    new_password: $("#new_password").val()
                                },
                                success: function(data) {
                                    if(data.ret === 1) {
                                        Swal.fire({
                                            text: data.msg,
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton: "btn btn-primary"
                                            }
                                        }).then(function (result) {
                                            if (result.isConfirmed) {
                                                modal.hide();
                                                location.reload();
                                            }
                                        });
                                    } else {
                                        getResult(data.msg, '', 'error');
                                    }
                                }
                            });

                            //form.submit(); // Submit form
                        }, 2000);
                    }
                });
            }
        });
    }

    return {
        // Public functions
        init: function () {
            initUpdatePassword();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUsersUpdatePassword.init();
});

// enable notify 
function KTUsersEnableNotify(type) {
    if (document.getElementById('notify_email').checked) {
        var types = type;
    }else if (document.getElementById('notify_telegram').checked) {
        var types = type;
    }
    $.ajax({
        type: "POST",
        url: "/user/enable_notify",
        dataType: "json",
        data: {
            notify_type: types
        },
        success: function(data) {}
    });
}

//reset ss connet passwd
function KTUsersResetPasswd() {
    $.ajax({
        type: "POST",
        url: "/user/reset_passwd",
        dataType: "json",
        data: {},
        success: function(data) {
            if(data.ret === 1) {
                Swal.fire({
                    text: data.msg,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                getResult(data.msg, '', 'error');
            }
        }
    });
}

// reset uuid
function KTUsersResetUUID() {

    $.ajax({
        type: "POST",
        url: "/user/reset_uuid",
        dataType: "json",
        data: {},
        success: function(data) {
            if(data.ret === 1) {
                Swal.fire({
                    text: data.msg,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                getResult(data.msg, '', 'error');
            }
        }
    });
}

// reset sub link
function KTUsersResetSubLink() {
    $.ajax({
        type: "POST",
        url: "/user/reset_sub_link",
        dataType: "json",
        data: {},
        success: function(data) {
            if(data.ret === 1) {
                Swal.fire({
                    text: data.msg,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                }).then(function (result) {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                getResult(data.msg, '', 'error');
            }
        }
    });
}

// show configure product modal 
function kTUserConfigureProductModal(id) {
    the_product_id = id;
    const html = $('#zero_product_'+id).html();
    const name = $('#zero_product_name_'+id).html();
    const price = $('#zero_product_price_'+id).html();
    const submitButton = document.querySelector('[data-kt-users-action="submit"]')
    $('#zero_modal_configure_product_inner_html').html(html);
    $('#zero_modal_configure_product_name').html(name + '&nbsp;X&nbsp;1');
    $('#zero_modal_configure_product_price').html(price + 'USD');
    $('#zero_modal_configure_product_total').html(price + 'USD');
    submitButton.setAttribute('onclick', 'KTUsersCreateOrder("purchase_product_order",' +id+')');
    $("#zero_modal_configure_product").modal("show");
}

// verify coupon
function KTUserVerifyCoupon() {
    $.ajax({
        type: "POST",
        url: "/user/verify_coupon",
        dataType: "json",
        data: {
            coupon_code: $("#zero_coupon_code").val(),
            product_id: the_product_id
        },
        success: function (data) {
            if (data.ret == 1) {
                document.getElementById('zero_modal_configure_product_total').innerHTML = data.total + 'USD';
            } else {
                getResult(data.msg, '', 'error');
            }
        }
    })
}
// create order
function KTUsersCreateOrder(type, product_id) {
    const submitButton = document.querySelector('[data-kt-users-action="submit"]');
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    switch (type) {
        case 'purchase_product_order':
            setTimeout(function () {
                $.ajax({
                    type: "POST",
                    url: "/user/order/create_order/"+type,
                    dataType: "json",
                    data: {
                        product_id: product_id,
                        coupon_code: $("#zero_coupon_code").val(),
                    },
                    success: function (data) {
                        if (data.ret == 1) {
                            setTimeout(function() {
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;
                                $(location).attr('href', '/user/order/' + data.order_id);
                            }, 1500);
                        } else {
                            getResult(data.msg, '', 'error');
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        }
                    }
                });
            }, 2000)
            break;
        case 'add_credit_order':
            setTimeout(function () {
                $.ajax({
                    type: "POST",
                    url: "/user/order/create_order/"+type,
                    dataType: "json",
                    data: {
                        add_credit_amount: $("#add_credit_amount").val()
                    },
                    success: function (data) {
                        if (data.ret == 1) {
                            setTimeout(function() {
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;
                                $(location).attr('href', '/user/order/' + data.order_id);
                            }, 1500);
                        } else {
                            getResult(data.msg, '', 'error');
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        }
                    }
                });
            }, 2000)
            break; 
    }
}

//pay for order
function KTUsersPayOrder(order_no) {
    const submitButton = document.querySelector('[data-kt-users-action="submit"]');
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    setTimeout(function () {
        $.ajax({
            type: "POST",
            url: "/user/order/pay_order",
            dataType: "json",
            data: {
                method: $("#payment_method a.active").attr("data-name"),
                order_no: order_no
            },
            success: function (data) {
                if (data.ret == 1) {
                    setTimeout(function() {
                        $(location).attr('href', data.url);
                        submitButton.removeAttribute('data-kt-indicator');
                        submitButton.disabled = false;
                    }, 1500);
                } else if (data.ret == 2){
                    Swal.fire({
                        text: data.msg,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                    submitButton.removeAttribute('data-kt-indicator');
                    submitButton.disabled = false;
                } else {
                    getResult(data.msg, '', 'error');
                    submitButton.removeAttribute('data-kt-indicator');
                    submitButton.disabled = false;
                }
            }
        });
    }, 2000);
}

// ticket
function KTUsersTicket(type, ticket_id, ticket_status) {
    const submitButton = document.querySelector('[data-kt-users-action="submit"]');
    submitButton.setAttribute('data-kt-indicator', 'on');
    submitButton.disabled = true;
    var text = editors.getData();
    switch (type) {
        case 'create_ticket':
            setTimeout(function () {
                $.ajax({
                    type: "POST",
                    url: "/user/ticket",
                    dataType: "json",
                    data: {
                        title: $("#zero_create_ticket_title").val(),
                        content: text
                    },
                    success: function (data) {
                        if (data.ret == 1) {
                            setTimeout(function() {
                                $(location).attr('href', '/user/ticket/'+data.tid+'/view');
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;
                            }, 1500);
                        } else {
                            getResult(data.msg, '', 'error');
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        }
                    }
                });
            }, 2000);
        break;
        case 'reply_ticket':
            setTimeout(function () {
                $.ajax({
                    type: "PUT",
                    url: "/user/ticket/"+ticket_id,
                    dataType: "json",
                    data: {
                        status: ticket_status,
                        content: text
                    },
                    success: function (data) {
                        if (data.ret == 1) {
                            setTimeout(function() {
                                location.reload();
                                submitButton.removeAttribute('data-kt-indicator');
                                submitButton.disabled = false;
                            }, 1500);
                        } else {
                            getResult(data.msg, '', 'error');
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        }
                    }
                });
            }, 2000);
        break;
    }
}

// show node 
function KTUsersShowNodeInfo(id, userclass, nodeclass) {
    nodeid = id;
    usersclass = userclass;
    nodesclass = nodeclass;
    if (usersclass >= nodesclass) {
        getLoad();
		$.ajax({
			type: "GET",
			url: "/user/nodeinfo/" + nodeid,
			dataType: "json",
			data: {},
			success: function(data) {
				if (data.ret == 1){
					if (data.sort == 11) {
						var content = data.url;
                        $("#zero_modal_vmess_node_info_remark").html(data.info.remark);
						$("#zero_modal_vmess_node_info_add").html(data.info.add);
						$("#zero_modal_vmess_node_info_port").html(data.info.port);
						$("#zero_modal_vmess_node_info_aid").html(data.info.aid);
                        $("#zero_modal_vmess_node_info_id").html(data.info.id);
						$("#zero_modal_vmess_node_info_net").html(data.info.net);
						$("#zero_modal_vmess_node_info_path").html(data.info.path);
						$("#zero_modal_vmess_node_info_host").html(data.info.host);
                        $("#zero_modal_vmess_node_info_servicename").html(data.info.servicename);
                        $("#zero_modal_vmess_node_info_type").html(data.info.type);
						$("#zero_modal_vmess_node_info_security").html(data.info.tls);
						$("#zero_modal_vmess_node_info_qrcode").html('<div class="pb-3" align="center" id="qrcode'+nodeid+'"></div>');
						$("#qrcode"  + nodeid).qrcode({
							width: 200,
							height: 200,
							render: "canvas",
							text: content
						});
						Swal.close();
						$("#zero_modal_vmess_node_info").modal('show');
					} else if ( data.sort == 14) {
						var content = data.url;
                        $("#zero_modal_trojan_node_info_remark").html(data.info.remark);
						$("#zero_modal_trojan_node_info_add").html(data.info.address);
						$("#zero_modal_trojan_node_info_port").html(data.info.port);						
                        $("#zero_modal_trojan_node_info_id").html(data.info.passwd);
						$("#zero_modal_trojan_node_info_host").html(data.info.host);
						$("#zero_modal_trojan_node_info_security").html(data.info.tls);
						$("#zero_modal_trojan_node_info_qrcode").html('<div class="pb-3" align="center" id="qrcode'+nodeid+'"></div>');
						$("#qrcode"  + nodeid).qrcode({
							width: 200,
							height: 200,
							render: "canvas",
							text: content
						});
						Swal.close();
						$("#nodeinfo-trojan-modal").modal('show');
					} else if (data.sort == 15) {
						var content = data.url;
                        $("#zero_modal_vless_node_info_remark").html(data.info.remark);
						$("#zero_modal_vless_node_info_add").html(data.info.add);
						$("#zero_modal_vless_node_info_port").html(data.info.port);
                        $("#zero_modal_vless_node_info_id").html(data.info.id);
						$("#zero_modal_vless_node_info_net").html(data.info.net);
						$("#zero_modal_vless_node_info_path").html(data.info.path);
						$("#zero_modal_vless_node_info_host").html(data.info.host);
                        $("#zero_modal_vless_node_info_servicename").html(data.info.servicename);
                        $("#zero_modal_vless_node_info_type").html(data.info.type);
						$("#zero_modal_vless_node_info_security").html(data.info.tls);
                        $("#zero_modal_vless_node_info_flow").html(data.info.flow);
						$("#zero_modal_vless_node_info_sni").html(data.info.sni);
						$("#zero_modal_vless_node_info_qrcode").html('<div class="pb-3" align="center" id="qrcode'+nodeid+'"></div>');
						$("#qrcode"  + nodeid).qrcode({
							width: 200,
							height: 200,
							render: "canvas",
							text: content
						});
						Swal.close();
						$("#nodeinfo-vless-modal").modal('show');
					} else if (data.sort == 0) {
						var content = data.url;
						$("#zero_modal_shadowsocks_node_info_remark").html(data.info.remark);
						$("#zero_modal_shadowsocks_node_info_address").html(data.info.address);
						$("#zero_modal_shadowsocks_node_info_port").html(data.info.port);
						$("#zero_modal_shadowsocks_node_info_method").html(data.info.method);
						$("#zero_modal_shadowsocks_node_info_passwd").html(data.info.passwd);
						$("#zero_modal_shadowsocks_node_info_qrcode").html('<div class="pb-3" align="center" id="qrcode'+nodeid+'"></div>');
						$("#qrcode"  + nodeid).qrcode({
							width: 200,
							height: 200,
							render: "canvas",
							text: content
						});
						Swal.close();
						$("#zero_modal_shadowsocks_node_info").modal('show');
					}
				} else {                   
					getResult(data.msg, "", "error");
				}
			}
		});
    } else {
        getResult("ζιδΈθΆ³", "", "error");
    }
}

//import sub url
function oneclickImport(client, subLink) {
    var sublink = {
      surfboard: "surfboard:///install-config?url=" + encodeURIComponent(subLink),
      quantumult: "quantumult://configuration?server=" + btoa(subLink).replace(/=/g, '') + "&filter=YUhSMGNITTZMeTl0ZVM1dmMyOWxZMjh1ZUhsNkwzSjFiR1Z6TDNGMVlXNTBkVzExYkhRdVkyOXVaZw",
      shadowrocket: "shadowrocket://add/sub://" + btoa(subLink),
      surge4: "surge3:///install-config?url=" + encodeURIComponent(subLink),
      clash: "clash://install-config?url=" + encodeURIComponent(subLink),
      sagernet: "sn://subscription?url=" + encodeURIComponent(subLink),
      ssr: "sub://" + btoa(subLink)
    }
    Swal.fire({
        title: "Whether to import subscription links",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Submit",
        cancelButtonText: "Discard",
        focusClose: false,
        focusConfirm: false,
    }).then((result) => {
        if (result.value) {
        window.location.href = sublink[client];
        }
    });
}

