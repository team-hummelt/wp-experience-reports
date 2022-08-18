<div class="experience-reports">
    <div class="container">

        <div class="card shadow-sm">
            <h5 class="card-header d-flex align-items-center bg-card py-4">
                <i class="bi bi-chat-square-text" style="font-size: 34px"></i>
                &nbsp;
                <?= __('WP-Report', 'wp-experience-reports') ?> <?= __('Settings', 'wp-experience-reports') ?></h5>

            <div id="cardFormulareWrapper" class="card-body pb-4" style="min-height: 72vh">
                <div class="d-flex align-items-center">
                    <h5 class="card-title">
                        <i id="extra-option-settings" class="font-yellow bi bi-arrow-right-circle"></i>
                        <span id="currentSideTitle"><?= __('User settings', 'wp-experience-reports') ?></span>
                    </h5>
                    <div class="ajax-status-spinner ms-auto"></div>
                </div>
                <hr class="mt-1">
                <div class="settings-btn-group d-block d-md-flex flex-wrap">
                    <button data-site="<?= __('User settings', 'wp-experience-reports') ?>"
                            type="button"
                            data-type="user-settings"
                            data-bs-toggle="collapse" data-bs-target="#collapseReportsUserSettingsSite"
                            class="btn-post-collapse btn btn-hupa btn-outline-secondary active" disabled>
                        <i class="bi bi-person-fill"></i>&nbsp;
                        <?= __('User settings', 'wp-experience-reports') ?>
                    </button>
                    <button data-site="<?= __('Plugin settings', 'wp-experience-reports') ?>"
                            type="button"
                            data-type="twig-settings"
                            id="btnExtraOption"
                            data-bs-toggle="collapse" data-bs-target="#collapseReportsPluginSettingsSite"
                            class="btn-post-collapse btn btn-hupa btn-outline-secondary d-none">
                        <i class="bi bi-gear"></i>&nbsp;
                        <?= __('Plugin settings', 'wp-experience-reports') ?>
                    </button>
                </div>
                <hr>
                <div id="post_display_parent">
                    <!--  TODO JOB WARNING STARTSEITE -->
                    <div class="collapse show" id="collapseReportsUserSettingsSite"
                         data-bs-parent="#post_display_parent">
                        <h6>
                            <i class="font-blue bi bi-arrow-circle"></i>
                            <?= esc_html__('Minimum requirement for using this Plugin', 'wp-experience-reports') ?>
                        </h6>
                        <hr>
                        <form class="send-ajax-experience-admin-settings">
                            <input type="hidden" name="method" value="update_er_settings">
                            <div class="row g-2">
                                <div class="col-xl-6 col">
                                    <div class="mb-3">
                                        <label for="capabilitySelect"
                                               class="form-label mb-1 strong-font-weight"><?= esc_html__('User Role', 'wp-experience-reports') ?>
                                        </label>
                                        <select name="user_role"
                                                id="capabilitySelect" class="form-select no-blur">
                                            <?php
                                            $select = apply_filters($this->basename . '/user_roles_select', '');
                                            foreach ($select as $key => $val):
                                                $key == get_option('experience_reports_user_role') ? $sel = 'selected' : $sel = ''; ?>
                                                <option value="<?= $key ?>" <?= $sel ?>><?= $val ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div> <!--section-->
                    <!--  TODO JOB WARNING Templates -->
                    <div class="collapse" id="collapseReportsPluginSettingsSite"
                         data-bs-parent="#post_display_parent">

                        <h5>
                            <i class="bi bi-arrow-right-short"></i>
                            <?= esc_html__('Templates', 'wp-experience-reports') ?>
                        </h5>
                        <b class="strong-font-weight">
                            <?= esc_html__('Template Path', 'wp-experience-reports') ?>:
                        </b>
                        <?= $this->main->get_twig_user_templates(); ?>
                        <hr>
                        <div class="d-flex flex-wrap extra-optionen-wrapper">
                            <button id="loadViewFileThree" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFolderThree"
                                    class="btn btn-blue mb-1 me-1">
                                <i class="bi bi-folder2-open"></i>&nbsp; <?= esc_html__('View files', 'wp-experience-reports') ?>
                            </button>
                            <button data-bs-toggle="collapse"
                                    data-bs-target="#collapseTemplateSettings"
                                    class="btn btn-blue mb-1 me-1">
                                <i class="bi bi-file-code"></i>&nbsp; <?= esc_html__('Template Settings', 'wp-experience-reports') ?>
                            </button>
                            <button class="btn-sync-templates btn btn-blue mb-1 ms-auto">
                                <i class="bi bi-shuffle"></i>&nbsp; <?= esc_html__('Synchronize templates', 'wp-experience-reports') ?>
                            </button>
                        </div>
                        <div id="settingsCollapseParent">
                            <div id="collapseFolderThree" class="collapse" data-bs-parent="#settingsCollapseParent">
                                <div id="reportPluginRoot" data-folder="<?= $this->main->get_twig_user_templates() ?>">
                                    <div class="three-wrapper show-form-input">
                                        <hr>
                                        <h6><i class="fa fa-folder-open-o"></i>
                                            <?= esc_html__('Folder', 'wp-experience-reports') ?>:
                                            <i>"report-templates"</i>
                                        </h6>
                                        <hr class="mt-1">
                                        <div id="container"></div>
                                        <hr>
                                        <div class="ordner-select"><?= esc_html__('Folder name', 'wp-experience-reports') ?></div>
                                        <button data-bs-toggle="collapse" data-bs-target="#collapseFolderThree"
                                                class="btn btn-blue-outline btn-sm mb-2 mt-3 btn-sm">
                                            <i class="bi bi-folder-fill"></i>&nbsp; <?= esc_html__('Close', 'wp-experience-reports') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div><!--parent-settings-->
                    </div>

                    <div id="collapseFolderThreeEdit" class="collapse" data-bs-parent="#post_display_parent">
                        <div class="card">
                            <h5 class="px-3">
                                <i class="bi bi-arrow-right-short"></i>
                                <?= esc_html__('Templates', 'wp-experience-reports') ?>
                                <small class="fs-6 small strong-font-weight d-block ">
                                    <?= esc_html__('Template Path', 'wp-experience-reports') ?>:
                                    <?= $this->main->get_twig_user_templates(); ?>
                                </small>
                            </h5>
                            <hr>
                            <div class="px-3 pb-1">
                                <button data-bs-toggle="collapse" data-bs-target="#collapseReportsPluginSettingsSite"
                                        class="btn btn-blue-outline mb-1 d-block">
                                    <i class="bi bi-reply-all-fill"></i>&nbsp; <?= esc_html__('back', 'wp-experience-reports') ?>
                                </button>
                            </div>
                            <div class="pre-twig-file">
                                <pre class="pre-templates" id="showTwigFile"></pre>
                            </div>

                            <div class="pb-3 px-3">
                                <button data-bs-toggle="collapse" data-bs-target="#collapseReportsPluginSettingsSite"
                                        class="btn btn-blue-outline mb-1 d-block">
                                    <i class="bi bi-reply-all-fill"></i>&nbsp; <?= esc_html__('back', 'wp-experience-reports') ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="collapseTemplateSettings" class="collapse" data-bs-parent="#post_display_parent">
                        <div class="card">
                            <div class="card-body">
                                <h5>
                                    <i class="bi bi-arrow-right-short"></i>
                                    <?= esc_html__('Templates', 'wp-experience-reports') ?>
                                </h5>
                                <hr>
                                <div class="col-xl-6 col-lg-10 col-12 mx-auto my-3">
                                    <form id="saveTemplateSettings">
                                        <input type="hidden" name="method" value="update-twig-templates">
                                        <?php
                                       //delete_option($this->basename . '_twig_templates');
                                        $templates = get_option($this->basename . '_twig_templates');
                                        foreach ($templates as $tmp): ?>
                                            <div id="<?= $tmp['id'] ?>" class="card shadow-sm mb-3">
                                                <div class="card-body">
                                                    <h5>File: <?= $tmp['file'] ?>
                                                    <small class="small fs-6 fw-normal d-block">
                                                        <?= esc_html__('Change template name', 'wp-experience-reports') ?>
                                                    </small>
                                                    </h5>
                                                    <hr>
                                                    <div class="mb-3">
                                                        <label for="tempBez<?= $tmp['id'] ?>"
                                                               class="form-label">
                                                            <?= esc_html__('Template name', 'wp-experience-reports') ?>
                                                        </label>
                                                        <input name="bezeichnung[<?= $tmp['id'] ?>]" type="text" value="<?= $tmp['name'] ?>"
                                                               class="form-control" id="tempBez<?= $tmp['id'] ?>"
                                                               required>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" name="isGallery[gallery#<?=$tmp['id']?>]"
                                                               type="checkbox" role="switch" id="isGallery<?=$tmp['id']?>"<?=$tmp['is_gallery'] ? 'checked' : ''?>>
                                                        <label class="form-check-label" for="isGallery<?=$tmp['id']?>">
                                                            <?= esc_html__('With gallery', 'wp-experience-reports') ?>
                                                        </label>
                                                    </div>
                                                    <hr>
                                                    <button data-type="delete_twig_template" data-id="<?= $tmp['id'] ?>" type="button" class="pluginActionsBtn btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        <button  type="submit" class="btn btn-blue mt-3">
                                            <i class="bi bi-save"></i>&nbsp; <?= esc_html__('Save changes', 'wp-experience-reports') ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div><!--parent-->
            </div>
        </div>
    </div>
</div>
<div id="snackbar-success"></div>
<div id="snackbar-warning"></div>