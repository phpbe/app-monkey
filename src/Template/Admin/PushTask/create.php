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
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Monkey.PushTask.pushTasks'); ?>">返回任务列表</el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                    <el-button size="medium" type="primary" :disabled="loading" @click="vueCenter.save();">下一步</el-button>
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
        <el-form ref="formRef" :model="formData">
            <div class="be-row">
                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-p-150 be-bc-fff">
                        <div><span class="be-c-red">*</span> 发布器：</div>
                        <div class="be-mt-50 be-b-eee" style="width: 400px; height: 240px; overflow-y: scroll;">
                            <div class="be-px-100 be-py-50" v-for="(pushDriver, pushDriverIndex) in pushDrivers">
                                <el-radio v-model="formData.push_driver_id" :label="pushDriver.id" @change="togglePushDriver(pushDriver);">{{pushDriver.name}}</el-radio>
                            </div>
                            <?php $formData['push_driver_id'] = $this->pushDriverId; ?>
                        </div>
                    </div>
                </div>

                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-pl-200 be-pt-200"></div>
                </div>

                <div class="be-col-24 be-xl-col">

                    <div class="be-p-150 be-bc-fff" style="height: 100%;" v-show="pushDriver != false">
                        <table class="monkey-task-form-table">
                            <tr>
                                <td>发布器名称：</td>
                                <td>{{pushDriver.name}}</td>
                            </tr>
                            <tr>
                                <td>版本：</td>
                                <td>{{pushDriver.version}}</td>
                            </tr>
                            <tr>
                                <td>创建时间：</td>
                                <td>{{pushDriver.create_time}}</td>
                            </tr>
                            <tr>
                                <td>更新时间：</td>
                                <td>{{pushDriver.update_time}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="be-row be-mt-200">
                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-p-150 be-bc-fff">
                        <div class="be-mt-100"><span class="be-c-red">*</span> 采集任务：</div>
                        <div class="be-mt-50 be-b-eee" style="width: 400px; height: 240px; overflow-y: scroll;">
                            <div class="be-px-100 be-py-50" v-for="(pullTask, pullTaskIndex) in pullTasks">
                                <el-radio v-model="formData.pull_task_id" :label="pullTask.id" @change="togglePullTask(pullTask);">{{pullTask.name}} </el-radio>
                            </div>
                            <?php $formData['pull_task_id'] = $this->pullTaskId;; ?>
                        </div>

                    </div>
                </div>

                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-pl-200 be-pt-200"></div>
                </div>

                <div class="be-col-24 be-xl-col">

                    <div class="be-p-150 be-bc-fff" style="height: 100%;" v-show="pullTask != false">
                        <table class="monkey-task-form-table">
                            <tr>
                                <td>采集任务名称：</td>
                                <td>{{pullTask.name}}</td>
                            </tr>
                            <tr>
                                <td>描述：</td>
                                <td><div v-html="pullTask.description"></div></td>
                            </tr>
                            <tr>
                                <td>版本：</td>
                                <td>{{pullTask.version}}</td>
                            </tr>
                            <tr>
                                <td>已采集文章数：</td>
                                <td>{{pullTask.content_count}}</td>
                            </tr>
                            <tr>
                                <td>创建时间：</td>
                                <td>{{pullTask.create_time}}</td>
                            </tr>
                            <tr>
                                <td>更新时间：</td>
                                <td>{{pullTask.update_time}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </el-form>
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
                pushDrivers: <?php echo json_encode($this->pushDrivers); ?>,
                pushDriver: false,

                pullTasks: <?php echo json_encode($this->pullTasks); ?>,
                pullTask: false,

                loading: false,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                togglePushDriver: function (pushDriver) {
                    this.pushDriver = pushDriver;
                },
                togglePullTask: function (pullTask) {
                    this.pullTask = pullTask;
                },
                save: function () {
                    if (this.pushDriver === false) {
                        this.$message.error("请选择一个发布器！");
                        return;
                    }

                    if (this.pullTask === false) {
                        this.$message.error("请选择一个采集任务！");
                        return;
                    }

                    let url = "<?php echo beAdminUrl('Monkey.PushTask.create'); ?>";
                    url += (url.indexOf("?") === -1 ? '?' : '&');
                    url += "push_driver_id=" + this.pushDriver.id;
                    url += "&pull_task_id=" + this.pullTask.id;
                    window.location.href = url;
                },
                cancel: function () {
                    window.onbeforeunload = null;
                    window.location.href = "<?php echo beAdminUrl('Monkey.PushTask.pushTasks'); ?>";
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