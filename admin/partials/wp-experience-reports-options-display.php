<div class="experience-reports">
    <div class="container">
        <div class="card shadow-sm">
            <h5 id="ExperienceReportsApiConnect" class="card-header d-flex align-items-center bg-card py-4">
                <i class="bi bi-chat-square-text" style="font-size: 34px"></i>
                &nbsp;
                <?= __('WP-Experience Reports', 'wp-experience-reports') ?> <?= __('Settings', 'wp-experience-reports') ?></h5>

            <div id="cardFormulareWrapper" class="card-body pb-4" style="min-height: 72vh">
                <div class="d-flex align-items-center">
                    <h5 class="card-title">
                        <i class="font-yellow bi bi-arrow-right-circle"></i>
                        <span id="currentSideTitle"><?= __('Settings', 'wp-experience-reports') ?></span>
                    </h5>
                    <div class="ajax-status-spinner ms-auto"></div>
                </div>
                <hr class="mt-1">
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
                                    $select = apply_filters($this->basename.'/user_roles_select', '');
                                    foreach ($select as $key => $val):
                                        $key == get_option('experience_reports_user_role') ? $sel = 'selected' : $sel = ''; ?>
                                        <option value="<?= $key ?>" <?= $sel ?>><?= $val ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<div id="snackbar-success"></div>
<div id="snackbar-warning"></div>