(function ($) {
    'use strict';
})(jQuery);

document.addEventListener("DOMContentLoaded", function (event) {
    let ajaxSpinner = document.querySelectorAll(".ajax-status-spinner");
    /**================================================
     ========== TOGGLE FORMULAR COLLAPSE BTN  ==========
     ===================================================
     */
    let pstSelectorColBtn = document.querySelectorAll("button.btn-post-collapse");
    if (pstSelectorColBtn) {
        let postCollapseEvent = Array.prototype.slice.call(pstSelectorColBtn, 0);
        postCollapseEvent.forEach(function (postCollapseEvent) {
            postCollapseEvent.addEventListener("click", function () {
                if (ajaxSpinner) {
                    let spinnerNodes = Array.prototype.slice.call(ajaxSpinner, 0);
                    spinnerNodes.forEach(function (spinnerNodes) {
                        spinnerNodes.innerHTML = '';
                    });
                }
                this.blur();
                if (this.classList.contains("active")) return false;
                let siteTitle = document.getElementById("currentSideTitle");
                let galerieCollapse = document.getElementById('collapseGalerieSite')
                let slideFormWrapper = document.getElementById('slideFormWrapper');
                siteTitle.innerText = this.getAttribute('data-site');
                let type = this.getAttribute('data-type');
                switch (type) {
                    case 'slider':
                        galerieCollapse.innerHTML = '';
                        break;
                    case'galerie':
                        slideFormWrapper.innerHTML = '';
                        break;
                }
                remove_active_btn();
                this.classList.add('active');
                this.setAttribute('disabled', true);
            });
        });

        function remove_active_btn() {
            for (let i = 0; i < postCollapseEvent.length; i++) {
                postCollapseEvent[i].classList.remove('active');
                postCollapseEvent[i].removeAttribute('disabled');
            }
        }
    }

    /**================================================
     ================ API XHR FORMULAR ================
     ==================================================
     */
    function api_xhr_experience_reports_form_data(data, is_formular = true, callback) {
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
        formData.append('action', 'EReportHandle');
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

    let pluginAdminSendFormTimeout;
    let pluginAdminSendFormular = document.querySelectorAll(".send-ajax-experience-admin-settings:not([type='button'])");
    if (pluginAdminSendFormular) {
        let formNodes = Array.prototype.slice.call(pluginAdminSendFormular, 0);
        formNodes.forEach(function (formNodes) {
            formNodes.addEventListener("keyup", plugin_input_ajax_handle, {passive: true});
            formNodes.addEventListener('touchstart', plugin_input_ajax_handle, {passive: true});
            formNodes.addEventListener('change', plugin_input_ajax_handle, {passive: true});

            function plugin_input_ajax_handle() {
                let spinner = Array.prototype.slice.call(ajaxSpinner, 0);
                spinner.forEach(function (spinner) {
                    spinner.innerHTML = `<div class="spinner-sm-border" role="status">
                                         <span class="visually-hidden">Loading...</span>
                                         </div>&nbsp; Saving...`;
                });
                clearTimeout(pluginAdminSendFormTimeout);
                pluginAdminSendFormTimeout = setTimeout(function () {
                    api_xhr_experience_reports_form_data(formNodes, true, formular_save_callback);
                }, 1000);
            }
        });
    }

    function formular_save_callback() {
        let data = JSON.parse(this.responseText);
        if(data.status) {

        }

        show_ajax_spinner(data);
    }

    /*======================================
    ========== AJAX SPINNER SHOW  ==========
    ========================================
    */
    function show_ajax_spinner(data, el = '') {
        let msg = '';
        let ajaxSpinner = document.querySelectorAll(".ajax-status-spinner");
        if (el) {
            ajaxSpinner = el;
        }
        if (data.status) {
            msg = '<i class="text-success bi bi-check2-circle"></i>&nbsp; Saved! Last: ' + data.msg;
        } else {
            msg = '<i class="text-danger bi bi-exclamation-triangle-fill"></i>&nbsp; ' + data.msg;
        }
        let spinner = Array.prototype.slice.call(ajaxSpinner, 0);
        spinner.forEach(function (spinner) {
            spinner.innerHTML = msg;
        });
    }

});

/**=========================================
 ========== AJAX RESPONSE MESSAGE ===========
 ============================================
 */
function success_message(msg) {
    let x = document.getElementById("snackbar-success");
    x.innerHTML = msg;
    x.className = "show";
    setTimeout(function () {
        x.className = x.className.replace("show", "");
    }, 3000);
}

function warning_message(msg) {
    let x = document.getElementById("snackbar-warning");
    x.innerHTML = msg;
    x.className = "show";
    setTimeout(function () {
        x.className = x.className.replace("show", "");
    }, 3000);
}
