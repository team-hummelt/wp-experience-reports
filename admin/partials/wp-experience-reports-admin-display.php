<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wwdh.de
 * @since      1.0.0
 *
 * @package    Post_Selector
 * @subpackage Post_Selector/admin/partials
 */

?>
<div class="experience-reports">
    <div class="container">
        <div class="card card-license shadow-sm">
            <h5 class="card-header d-flex align-items-center bg-card py-4">
                <i class="bi bi-chat-square-text d-block mt-2" style="font-size: 2rem"></i>&nbsp;
                <?= __('Experience Reports', 'wp-experience-reports') ?> </h5>
            <div class="card-body pb-4" style="min-height: 72vh">
                <div class="d-flex align-items-center">
                    <h5 class="card-title"><i
                                class="hupa-color fa fa-arrow-circle-right"></i> <?= __('Experience Reports', 'wp-experience-reports') ?>
                        / <span id="currentSideTitle"><?= __('Gallery Slider', 'wp-experience-reports') ?></span>
                    </h5>
                </div>
                <hr>
                <div class="settings-btn-group d-block d-md-flex flex-wrap">
                    <button data-site="<?= __('Gallery Slider', 'wp-experience-reports') ?>"
                            type="button"
                            data-type="slider"
                            id="btnDataSlider"
                            data-bs-toggle="collapse" data-bs-target="#collapsePostSelectOverviewSite"
                            class="btn-post-collapse btn btn-hupa btn-outline-secondary active" disabled>
                        <i class="bi bi-signpost-2"></i>&nbsp;
                        <?= __('Report Gallery Slider', 'wp-experience-reports') ?>
                    </button>

                    <button data-site="<?= __('Report Gallery', 'wp-experience-reports') ?>"
                            data-type="galerie"
                            type="button" id="postEditCollapseBtn"
                            data-bs-toggle="collapse" data-bs-target="#collapseGalerieSite"
                            class="btn-post-collapse btn btn-hupa btn-outline-secondary disabled">
                        <i class="bi bi-images"></i>&nbsp;
                        <?= __('Report Gallery', 'wp-experience-reports') ?>
                    </button>

                </div>
                <hr>
                <div id="post_display_parent">
                    <!--  TODO JOB WARNING STARTSEITE -->
                    <div class="collapse show" id="collapsePostSelectOverviewSite"
                         data-bs-parent="#post_display_parent">
                        <div class="border rounded mt-1 mb-3 shadow-sm p-3 bg-custom-gray" style="min-height: 53vh">
                            <div class="d-flex align-items-center">
                                <h5 class="card-title">
                                    <i class="font-blue bi bi-signpost-2"></i>&nbsp;<?= __('Experience Reports Gallery Slider', 'wp-experience-reports') ?>
                                </h5>
                                <div class="ajax-status-spinner ms-auto d-inline-block mb-2 pe-2"></div>
                            </div>
                            <hr class="mt-1">
                            <div class="d-flex flex-wrap">
                                <button data-type="insert" class="load-slider-temp btn btn-blue" disabled>
                                    <i class="bi bi-plus-circle"></i>&nbsp; <?= __('add Slider', 'wp-experience-reports') ?>
                                </button>

                                <button data-bs-toggle="modal" data-bs-target="#demoModal"
                                        class="btn btn-blue-outline btn-sm ms-auto" disabled>
                                    <?= __('add Demo', 'wp-experience-reports') ?>

                                </button>
                            </div>
                            <hr>
                            <h5 class="text-center"><i class="text-danger bi bi-signpost-2"></i>&nbsp;
                                <?= __('Experience Reports Gallery not installed.', 'wp-experience-reports') ?></h5>
                        </div>
                        <div id="slideFormWrapper"></div>
                    </div>
                    <!--  TODO JOB WARNING Galerie -->
                    <div class="collapse" id="collapseGalerieSite" data-bs-parent="#post_display_parent"></div>
                </div><!--parent-->
            </div>
            <small class="card-body-bottom" style="right: 1.5rem">DB: <i
                        class="text-alert"><?= $this->main->get_db_version() ?></i> | Version:
                <i class="text-alert">v<?= $this->version ?></i>
            </small>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="formDeleteModal" tabindex="-1" aria-labelledby="formDeleteModal"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-card">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i
                                class="text-danger fa fa-times"></i>&nbsp; Abbrechen
                    </button>
                    <button type="button" data-bs-dismiss="modal"
                            class="btn-delete-items btn btn-danger">
                        <i class="fa fa-trash-o"></i>&nbsp; löschen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="demoModal" tabindex="-1" aria-labelledby="demoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-card">
                    <h5 class="modal-title" id="demoModalLabel"><i class="fa fa-sliders"></i>&nbsp; Post Selector Demos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="form-modal mb-2 p-3">
                    <form class="send-bs-form-jquery-ajax-formular" action="#" method="post">
                        <input type="hidden" name="type" value="demo">
                        <input type="hidden" name="method" value="slider-form-handle">
                        <input class="modalAction" type="hidden" name="action">
                        <input class="modalNonce" type="hidden" name="_ajax_nonce">
                        <label for="inputDemoSlider" class="form-label">Demo auswählen</label>
                        <select class="form-select" name="demo_type" id="inputDemoSlider">
                            <option value="1">Beitrags Slider volle Breite</option>
                            <option value="2">wechselndes Einzelbild</option>
                        </select>
                        <button type="submit" data-bs-dismiss="modal" class="btn btn-blue btn-sm mt-3">auswählen und
                            Speichern
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!--MODAL ADD GALERIE-->
    <div class="modal fade" id="galerieHandleModal" tabindex="-1" aria-labelledby="addGalerieModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg">
            <div class="modal-content"></div>
        </div>
    </div>
</div>

<template id="delete-template">
    <swal-title>
        <span class="delete-swal-title">Kunde</span> wirklich löschen?
    </swal-title>
    <swal-icon type="warning" color="#d73814"></swal-icon>
    <swal-html>
        <div class="swal-second-txt">
            <h6 class="text-center">Daten werden unwiderruflich gelöscht!</h6>
        </div>
    </swal-html>
    <swal-button type="cancel">
        Abbrechen
    </swal-button>
    <swal-button color="#d73814" type="confirm">
        Löschen
    </swal-button>
    <swal-param name="allowEscapeKey" value="false"/>
    <swal-param
            name="customClass"
            value='{ "popup": "delete-swal" }'/>
</template>
<div id="snackbar-success"></div>
<div id="snackbar-warning"></div>
