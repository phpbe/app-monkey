<be-head>
    <style>
        .monkey-task-form-table {
            border: none;
            border-collapse: collapse;
        }

        .monkey-task-form-table td {
            padding: .5rem;
            vertical-align: middle;
        }
    </style>
</be-head>


<be-north>
    <div class="be-north" id="be-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Monkey.Task.tasks'); ?>">返回任务列表</el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        let vueNorth = new Vue({
            el: '#be-north',
            data: {
                loading: false,
            }
        });
    </script>
</be-north>


<be-page-content>
    <?php
    $formData = [];
    $uiItems = new \Be\AdminPlugin\UiItem\UiItems();
    $rootUrl = \Be\Be::getRequest()->getRootUrl();
    ?>

    <div id="app" v-cloak>
        <div class="be-p-150 be-bc-fff">

            <div class="be-fs-110 be-fw-bold">通过浏览器访问以下网址进行采集操作：</div>

            <el-tag class="be-mt-100"><?php echo $this->pullTask->start_page; ?></el-tag>

            <div class="be-mt-200 be-c-999">
                请先确认您已安装了了以下内容 ：
                <ol>
                    <li>
                        浏览器油猴插件。<el-link type="primary" href="https://www.phpbe.com/app-monkey/doc/tampe-rmonkey">安装教程</el-link>
                    </li>
                    <li>
                        任务（<?php echo $this->pullTask->name; ?>）的采集脚本：
                        <el-link type="primary" href="<?php echo $this->pullTask->start_page; ?>">安装</el-link>
                    </li>
                </ol>
            </div>

            <div class="be-mt-200">
                <a class="be-btn be-btn-main" type="primary" href="<?php echo $this->pullTask->start_page; ?>" rel="noreferrer">
                    <i class="el-icon-video-play"></i> 打开该页面进行采集
                </a>
            </div>


            <div class="be-mt-200 be-c-999">
                更多操作指南，请访问：<el-link type="primary" href="https://www.phpbe.com/app-monkey" target="_blank">https://www.phpbe.com/app-monkey</el-link>
            </div>

        </div>
    </div>

    <?php
    echo $uiItems->getJs();
    echo $uiItems->getCss();
    ?>

    <script>
        let vueCenter = new Vue({
            el: '#app',
            data: {
                formData: <?php echo json_encode($formData); ?>,

                loading: false,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {

                cancel: function () {
                    window.onbeforeunload = null;
                    window.location.href = "<?php echo beAdminUrl('Monkey.PullTask.pullTasks'); ?>";
                },

                <?php
                echo $uiItems->getVueMethods();
                ?>
            }
            <?php
            echo $uiItems->getVueHooks();
            ?>
        });

    </script>

</be-page-content>