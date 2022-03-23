document.addEventListener("DOMContentLoaded", function (event) {
    let ajaxSpinner = document.querySelectorAll(".ajax-status-spinner");
    let apiPublicSettingsWrapper = document.getElementById('publicApiSettings');
    let TwigTemplates = document.getElementById('extensionCollapseParent');
    if (TwigTemplates) {
        load_extension_preview_data();
    }
    let btnBack = document.querySelector('.btn-extension-back');
    let apiErrMsg = document.getElementById('apiErrMsg');
    let extensionOverview = document.querySelector('.extension-overview');

    /**================================================
     ================ API XHR FORMULAR ================
     ==================================================
     */
    function api_xhr_extension_form_data(data, is_formular = true, callback) {
        let xhr = new XMLHttpRequest();
        let formData = new FormData();

        if (is_formular) {
            let input = new FormData(data);
            for (let [name, value] of input) {
                formData.append(name, value);
            }
        } else {
            for (let [name, value] of Object.entries(data)) {
                formData.append(name, value);
            }
        }
        formData.append('_ajax_nonce', report_ajax_obj.nonce);
        formData.append('action', 'EReportAPIHandle');
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (typeof callback === 'function') {
                    xhr.addEventListener("load", callback);
                    return false;
                } else {
                    let data = JSON.parse(this.responseText);
                    if (data.status) {
                        success_message(data.msg);
                    } else {
                        warning_message('Error: no return');
                    }
                }
            }
        }
        xhr.open('POST', report_ajax_obj.ajax_url, true);
        xhr.send(formData);
    }

    function load_extension_preview_data() {
        let formData = {
            'method': 'load_extension_preview_data',
            'template': 'overview',
            'target': '#twigRenderOverview'
        }
        api_xhr_extension_form_data(formData, false, extension_preview_data_callback)
    }

    function extension_preview_data_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            apiErrMsg.classList.add('d-none');
            extensionOverview.classList.remove('d-none');
            let template = document.querySelector(data.target);
            template.innerHTML = data.template;
            extension_preview_btn_execute();
        } else {
            apiErrMsg.classList.remove('d-none');
            extensionOverview.classList.add('d-none');
        }
    }

    function extension_preview_btn_execute() {
        let btnExecute = document.querySelectorAll('.btn-extension-execute');
        if (btnExecute) {
            let method = '';
            let license = '';
            let nodes = Array.prototype.slice.call(btnExecute, 0);
            nodes.forEach(function (nodes) {
                nodes.addEventListener("click", function (e) {
                    let id = nodes.getAttribute('data-id');

                    let template = nodes.getAttribute('data-template');
                    let target = nodes.getAttribute('data-bs-target');

                    if (template == 'license') {
                        license = nodes.getAttribute('data-license');
                        method = 'load_license_data';
                        let formData = {
                            'method': method,
                            'license': license
                        }
                        api_xhr_extension_form_data(formData, false, extension_load_license_callback);

                        return false;
                    }
                    else {
                        switch (template){
                            case 'download':
                                let formData = {
                                    'method' : 'load_twig_template',
                                    'license': nodes.getAttribute('data-license'),
                                    'url_id': nodes.getAttribute('data-urlid'),
                                    'template': template,
                                    'extension': id,
                                    'target': target,
                                }
                                api_xhr_extension_form_data(formData, false, extension_load_template_callback);
                                return false;
                        }

                        method = 'load_twig_template';
                        license = ''
                    }
                    let formData = {
                        'method': method,
                        'template': template,
                        'extension': id,
                        'target': target,
                        'license': license
                    }
                    api_xhr_extension_form_data(formData, false, extension_load_template_callback);

                });
            });
        }
    }

    function download_form_event(){
        let btnDownloadExtension = document.getElementById('downloadExtension');
        if(btnDownloadExtension){
            btnDownloadExtension.addEventListener("click", function (e) {
                let loadIcon = document.querySelector('.ajax-load-extension');
                loadIcon.classList.remove('d-none');
                api_xhr_extension_form_data(btnDownloadExtension.form, true, download_event_callback);
            });
        }
    }

    function download_event_callback() {
        let data = JSON.parse(this.responseText);
        if(data.status) {

            if (data.confirm_dialog) {
                Swal.fire({
                    position: 'top-end',
                    title: data.title,
                    text: data.msg,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
                let loadIcon = document.querySelector('.ajax-load-extension');
                loadIcon.classList.add('d-none');
            } else {
                success_message(data.msg);
            }
        } else {
            warning_message(data.msg)
        }
    }

    let btnBackToOverview = document.querySelector('.btn-extension-back');
    if (btnBackToOverview) {
        btnBackToOverview.addEventListener("click", function (e) {
            btnBackToOverview.classList.add('d-none');

        });
    }

    function extension_load_template_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            let render = document.querySelector(data.target);
            if (render) {
                btnBack.classList.remove('d-none');
                render.innerHTML = data.template;
                extension_load_formular();
                download_form_event();
            }
        }
    }

    let collapseLicense = document.querySelectorAll('.collapseLicense');
    if (collapseLicense) {
        let nodes = Array.prototype.slice.call(collapseLicense, 0);
        let btnBackOverview = document.querySelector('.btn-extension-back');
        nodes.forEach(function (nodes) {
            nodes.addEventListener('hide.bs.collapse', function () {
                btnBackOverview.classList.add('d-none');
                load_extension_preview_data();
            });
        });
    }

    function extension_load_formular() {
        let activateExtensionForm = document.getElementById('activateExtension');
        if (activateExtensionForm) {
            activateExtensionForm.addEventListener("submit", function (e) {
                api_xhr_extension_form_data(activateExtensionForm, true, public_api_activated_callback);
                e.preventDefault();
            });
        }
    }

    function public_api_activated_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            let detailId = document.getElementById('extensionActivate');
            btnBack.classList.add('d-none');
            detailId.innerHTML = data.template;
        } else {
            document.getElementById('activateExtension').reset();
            alert(data.msg, 'danger', '#ajaxMsg');
        }
    }

    function extension_load_license_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            let detailId = document.getElementById('twigRenderLicense');
            let LicenseCollapse = document.getElementById('extensionLicense')
            LicenseCollapse.setAttribute('data-bs-toggle', 'true');
            detailId.innerHTML = data.template;
        } else {
            alert(data.msg, 'danger', '#ajaxMsg');
        }
    }


    let expReportsApiConnect = document.getElementById('ExperienceReportsApiConnect');
    if (expReportsApiConnect) {
        expReportsApiConnect.addEventListener("dblclick", function (e) {
            apiPublicSettingsWrapper.innerHTML = '';
            let dataType = this.getAttribute('data-type');
            let formData = {
                'method': 'get_public_api_commands_select',
                'type_response':dataType
            }
            api_xhr_extension_form_data(formData, false, select_public_api_callback);
        });
    }

    function select_public_api_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            cardFormulareWrapper.classList.add('d-none');
            apiPublicSettingsWrapper.insertAdjacentHTML('afterbegin', data.template);
            bsApiAjaxFormular();
            btn_extension_preview_actions(data);
        }
    }

    function btn_extension_preview_actions(data){
        let btnActions = document.querySelectorAll('.btn-preview-actions');
        let btnNodes = Array.prototype.slice.call(btnActions, 0);
        btnNodes.forEach(function (btnNodes) {
            btnNodes.addEventListener("click", function (e) {
                switch (data.type){
                    case 'close_command':
                        document.getElementById('cardFormulareWrapper').classList.remove('d-none');
                        document.getElementById('publicApiSettings').innerHTML = '';
                        break;
                }
            });
        });
    }

    function bsApiAjaxFormular() {
        let clickApiPublicFormularButton = document.querySelectorAll('.api-public-formular');
        if (clickApiPublicFormularButton) {
            let nodes = Array.prototype.slice.call(clickApiPublicFormularButton, 0);
            nodes.forEach(function (nodes) {
                nodes.addEventListener("submit", function (e) {
                    api_xhr_extension_form_data(nodes, true, public_api_command_callback);
                    e.preventDefault();
                });
            });
        }
    }

    function public_api_command_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            apiPublicSettingsWrapper.innerHTML = '';
            cardFormulareWrapper.classList.remove('d-none')
            remove_alert();
            success_message(data.msg);
        } else {
            alert(data.msg, 'danger mt-3', '#publicApiSettings .responseMessage');
            warning_message(data.msg);
        }
    }



});

function change_command_select(e) {
    e.blur();
    let form = e.form;
    let extraCommand = form.querySelector('#InputPublicExtra');
    switch (e.value) {
        case '2':
            extraCommand.removeAttribute('disabled');
            break;
        default:
            extraCommand.setAttribute('disabled', 'disabled');
    }
}

function alert(message, type, selector) {
    remove_alert()
    let wrapper = document.createElement('div');
    wrapper.classList.add('alert-wrapper');
    let alertPlaceholder = document.querySelector(selector)
    wrapper.innerHTML = '<div class="alert alert-' + type + ' alert-dismissible" role="alert"><i class="fa fa-exclamation-triangle"></i>&nbsp; ' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>'
    alertPlaceholder.append(wrapper);
}

function remove_alert() {
    let alertWrapper = document.querySelector('.alert-wrapper');
    if (alertWrapper) {
        alertWrapper.remove();
    }
}

function copy_data_client(e, rand = false) {
    let secretId = e.getAttribute('data-id');
    let el = document.createElement('textarea');
    let supId = '';
    el.value = secretId;

    el.setAttribute('readonly', '');
    el.style = {position: 'absolute', left: '-100vw'};
    document.body.appendChild(el);
    el.select();
    document.execCommand('copy');
    document.body.removeChild(el);
    if (rand) {
        supId = rand;
    } else {
        supId = el.value;
    }

    //let info = document.querySelector('#info'+supId);
    let info = jQuery('#info' + supId);
    info.animate({opacity: '1'}, "700");
    info.animate({opacity: '0'}, "9000");

}


