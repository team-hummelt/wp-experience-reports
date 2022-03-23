document.addEventListener("DOMContentLoaded", function (event) {

    /**========================================================
     ================ AJAX PUBLIC XHR FORMULAR ================
     ==========================================================
     */
    function api_xhr_experience_reports_public_form_data(data, is_formular = true, callback) {
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
        formData.append('_ajax_nonce', report_public_obj.nonce);
        formData.append('action', 'EReportPublicHandle');
        xhr.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if (typeof callback === 'function') {
                    xhr.addEventListener("load", callback);
                    return false;
                }
            }
        }
        xhr.open('POST', report_public_obj.ajax_url, true);
        xhr.send(formData);
    }

    let btnReportsActionButton = document.querySelectorAll('.experience-report-actions-button');
    if (btnReportsActionButton) {
        let nodes = Array.prototype.slice.call(btnReportsActionButton, 0);
        nodes.forEach(function (nodes) {
            nodes.addEventListener("click", function (e) {
                let dataMethod = nodes.getAttribute('data-method');
                e.preventDefault();
                nodes.blur();
                switch (dataMethod) {
                    case 'load-more-action':
                        let wrapperId = '';
                        let dataTotal = nodes.getAttribute('data-total');
                        let limit = nodes.getAttribute('data-limit');
                        let postId = nodes.getAttribute('data-post-id');
                        let loaded = nodes.getAttribute('data-loaded');
                        let catId = nodes.getAttribute('data-catId');
                        let parentContainer = nodes.parentElement.parentElement.parentElement;
                        let wrapper = parentContainer.querySelector('.experience-reports-wrapper');
                        if (wrapper.hasAttributes('data-id')) {
                            wrapperId = wrapper.getAttribute('data-id');
                        } else {
                            wrapperId = false;
                        }
                        nodes.classList.add('btn-' + wrapperId);
                        let formData = {
                            'method': dataMethod,
                            'total': dataTotal,
                            'limit': limit,
                            'post_id': postId,
                            'loaded': loaded,
                            'wrapper_id': wrapperId,
                            'cat_id': catId,

                        };
                        api_xhr_experience_reports_public_form_data(formData, false, load_more_post_callback)

                        break;
                }
            });
        });
    }

    function load_more_post_callback() {
        let data = JSON.parse(this.responseText);
        if (data.status) {
            if (data.wrapperId) {
                let wrapperContainer = document.getElementById('report-' + data.wrapperId);
                let moreBtn = document.querySelector('.btn-' + data.wrapperId);
                if (data.showBtn) {
                    moreBtn.setAttribute('data-loaded', data.loaded);
                } else {
                    moreBtn.classList.add('d-none');
                }
                wrapperContainer.insertAdjacentHTML('beforeend', data.template);
                load_splide_plugin();
            }
        }
    }

    let selectedChangeKategorie = document.querySelector('.report-change-select');
    if (selectedChangeKategorie) {
        selectedChangeKategorie.addEventListener("change", function (e) {
            selectedChangeKategorie.blur();
            let allCats = document.querySelectorAll('.experience-reports-content')
            if (allCats.length) {
                let nodes = Array.prototype.slice.call(allCats, 0);
                nodes.forEach(function (nodes) {
                    nodes.classList.remove('animate__fadeIn');
                    nodes.classList.remove('animate__fadeOut');
                    if (selectedChangeKategorie.value) {
                        if (nodes.classList.contains('reports-category-' + selectedChangeKategorie.value)) {
                            nodes.classList.add('show-category');
                            nodes.classList.add('animate__fadeIn');
                            nodes.classList.remove('d-none');
                        } else {
                            nodes.classList.remove('animate__fadeIn');
                            nodes.classList.add('d-none');
                        }
                    } else {
                        nodes.classList.add('animate__fadeIn');
                        nodes.classList.remove('d-none')
                    }
                });
            }
        });
    }

    function load_splide_plugin() {
        let wow = new WOW(
            {
                boxClass: 'wow',
                animateClass: 'animate__animated',
                offset: 0,
                mobile: true,
                live: true,
                callback: function (box) {
                },
                scrollContainer: null,
                resetAnimation: true,
            }
        );
        wow.init();

        let siteGalerie = document.querySelector('.experience-reports');
        let slideGalerie = document.getElementById('blueimp-gallery-slides');
        if (!slideGalerie) {

            let html = `
   <div id="blueimp-gallery-slides"
     class="blueimp-gallery blueimp-gallery-controls"
     aria-label="image gallery"
     aria-modal="true"
     role="dialog">
    <div class="slides" aria-live="polite"></div>
    <h3 class="title text-white fs-4"></h3>
    <a class="prev"
       aria-controls="blueimp-gallery"
       aria-label="previous slide"
       aria-keyshortcuts="ArrowLeft">
    </a>
    <a class="next"
       aria-controls="blueimp-gallery"
       aria-label="next slide"
       aria-keyshortcuts="ArrowRight">
    </a>
    <a class="close"
       aria-controls="blueimp-gallery"
       aria-label="close"
       aria-keyshortcuts="Escape">
    </a>
    <a class="play-pause"
       aria-controls="blueimp-gallery"
       aria-label="play slideshow"
       aria-keyshortcuts="Space"
       aria-pressed="false"
       role="button">
    </a>
    <ol class="indicator"></ol>
    </div>`;
            if (siteGalerie) {
                siteGalerie.insertAdjacentHTML('afterend', html);
            }

        }

        let singleGalerie = document.getElementById('blueimp-gallery-single');
        if (!singleGalerie) {
            let html = `
   <div id="blueimp-gallery-single"
     class="blueimp-gallery blueimp-gallery-controls"
     aria-label="image gallery"
     aria-modal="true"
     role="dialog">
    <div class="slides" aria-live="polite"></div>
    <h3 class="title text-white fs-4"></h3>
    <a class="close"
       aria-controls="blueimp-gallery"
       aria-label="close"
       aria-keyshortcuts="Escape">
    </a>
    </div>`;
            if (siteGalerie) {
                siteGalerie.insertAdjacentHTML('afterend', html);
            }
        }

        let splideImgContainer = document.querySelectorAll('.splide');
        if (splideImgContainer) {
            let splideNode = Array.prototype.slice.call(splideImgContainer, 0);
            splideNode.forEach(function (splideNode) {
                let splideRand = splideNode.getAttribute('data-rand');
                let splideId = splideNode.getAttribute('data-id');
                let isChance = false;
                if (splideNode.hasAttribute('data-chance')) {
                    isChance = splideNode.getAttribute('data-chance')
                } else {
                    isChance = false;
                }
                if (splideNode.classList.contains('more-splide')) {
                    set_new_splide_instance_settings(splideRand, splideId, isChance);
                }
            });

            /**============================================
             ========== SLIDER AJAX DATEN SENDEN ==========
             ==============================================
             */
            function set_new_splide_instance_settings(rand, slideId, isChance) {
                let xhr = new XMLHttpRequest();
                let formData = new FormData();
                xhr.open('POST', reports_gallery_ajax_obj.ajax_url, true);
                formData.append('_ajax_nonce', reports_gallery_ajax_obj.nonce);
                formData.append('action', 'ERGHandlePublic');
                formData.append('method', 'get_slider_settings');
                formData.append('id', slideId);
                formData.append('rand', rand);
                xhr.send(formData);
                //Response
                xhr.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        let data = JSON.parse(this.responseText);
                        if (data.status) {
                            let settings = data.sendSettings;
                            let interVal = '';
                            let newSpeed = '';
                            if (isChance) {
                                interVal = getRandomInt(settings.interval / 1.2, settings.interval)
                                newSpeed = getRandomInt(settings.speed - 100, settings.speed);
                            } else {
                                interVal = settings.interval;
                                newSpeed = settings.speed;
                            }

                            new Splide('.splide.more-splide' + rand, {
                                arrows: settings.arrows,
                                autoHeight: settings.autoHeight,
                                autoWidth: settings.autoWidth,
                                autoplay: settings.autoplay,
                                cover: settings.cover,
                                drag: settings.drag,
                                flickPower: settings.flickPower,
                                focus: settings.focus,
                                gap: settings.gap,
                                height: settings.height,
                                heightRatio: settings.heightRatio,
                                interval: interVal,
                                keyboard: settings.keyboard,
                                lazyLoad: settings.lazyLoad,
                                pagination: settings.pagination,
                                pauseOnFocus: settings.pauseOnFocus,
                                pauseOnHover: settings.pauseOnHover,
                                perMove: settings.perMove,
                                perPage: settings.perPage,
                                preloadPages: settings.preloadPages,
                                rewind: settings.rewind,
                                rewindSpeed: settings.rewindSpeed,
                                slideFocus: settings.slideFocus,
                                speed: newSpeed,
                                start: settings.start,
                                type: settings.type,
                                width: settings.width,
                                trimSpace: settings.trimSpace,
                                breakpoints: {
                                    1400: {
                                        perPage: settings.perPageXxl,
                                        gap: settings.gapXxl,
                                        height: settings.heightXxl,
                                        width: settings.widthXxl
                                    },
                                    1200: {
                                        perPage: settings.perPageXl,
                                        gap: settings.gapXl,
                                        height: settings.heightXl,
                                        width: settings.widthXl
                                    },
                                    992: {
                                        perPage: settings.perPageLg,
                                        gap: settings.gapLg,
                                        height: settings.heightLg,
                                        width: settings.widthLg
                                    },
                                    768: {
                                        perPage: settings.perPageMd,
                                        gap: settings.gapMd,
                                        height: settings.heightMd,
                                        width: settings.widthMd
                                    },
                                    576: {
                                        perPage: settings.perPageSm,
                                        gap: settings.gapSm,
                                        height: settings.heightSm,
                                        width: settings.widthSm
                                    },
                                    450: {
                                        perPage: settings.perPageXs,
                                        gap: settings.gapXs,
                                        height: settings.heightXs,
                                        width: settings.widthXs
                                    },
                                }
                            }).mount();
                        }
                    }
                }
            }
        }

        let postSelectorGrid = document.querySelectorAll(".post-selector-grid");
        if (postSelectorGrid) {
            let msnry;
            let gridNodes = Array.prototype.slice.call(postSelectorGrid, 0);
            gridNodes.forEach(function (gridNodes) {
                imagesLoaded(gridNodes, function () {
                    msnry = new Masonry(gridNodes, {
                        itemSelector: '.grid-item',
                        percentPosition: true
                    });
                });
            });
        }

        let blueImpLightbox = document.querySelectorAll(".light-box-controls");
        if (blueImpLightbox) {
            let lightBoxNodes = Array.prototype.slice.call(blueImpLightbox, 0);
            lightBoxNodes.forEach(function (lightBoxNodes) {
                lightBoxNodes.addEventListener("click", function (e) {
                    let target = e.target
                    let link = target.src ? target.parentNode : target;
                    let control = link.getAttribute('data-control');
                    if (!control) {
                        return false;
                    }
                    let options;
                    switch (control) {
                        case 'control':
                            options = {
                                container: '#blueimp-gallery-slides',
                                index: link,
                                event: e,
                                toggleControlsOnSlideClick: false,
                            }
                            break;
                        case 'single':
                            options = {
                                container: '#blueimp-gallery-single',
                                index: link,
                                event: e,
                                enableKeyboardNavigation: false,
                                emulateTouchEvents: false,
                                fullscreen: false,
                                displayTransition: false,
                                toggleControlsOnSlideClick: false,
                            }
                            break;
                    }
                    let links = this.querySelectorAll('a.img-link')
                    blueimp.Gallery(links, options)
                });
            });
        }
    }

    const imageObserver = new IntersectionObserver((entries, imgObserver) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const lazyImage = entry.target
                lazyImage.src = lazyImage.dataset.src
                lazyImage.classList.remove("lazy-image");
                imgObserver.unobserve(lazyImage);
            }
        })
    });

    const postImgArr = document.querySelectorAll('img.lazy-image');
    if (postImgArr) {
        postImgArr.forEach((postImg) => {
            imageObserver.observe(postImg);
        });
    }

    function getRandomInt(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min)) + min;
    }

    function getRandomIntInclusive(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }


});
