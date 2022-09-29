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
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Monkey.PullTask.pullTasks'); ?>">返回任务列表</el-link>
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
                 <div class="be-col-auto">

                     <div class="be-p-150 be-bc-fff">
                     <div><span class="be-c-red">*</span> 采集器：</div>
                     <div class="be-mt-50 be-b-eee" style="min-height: 300px; height: calc(100vh - 200px); overflow-y: scroll;">
                         <div class="be-px-100 be-py-50" v-for="(pullDriver, pullDriverIndex) in pullDrivers">
                             <el-radio v-model="formData.pull_driver_id" :label="pullDriver.id" @change="toggle(pullDriver);">{{pullDriver.name}}</el-radio>
                         </div>
                         <?php $formData['pull_driver_id'] = ''; ?>
                     </div>

                     </div>
                 </div>

                 <div class="be-col-auto">
                     <div class="be-pl-150"></div>
                 </div>

                 <div class="be-col">

                     <div class="be-p-150 be-bc-fff" style="height: 100%;" v-show="pullDriver != false">
                         <table class="monkey-task-form-table">
                             <tr>
                                 <td>采集器名称：</td>
                                 <td>{{pullDriver.name}}</td>
                             </tr>
                             <tr>
                                 <td>描述：</td>
                                 <td><div v-html="pullDriver.description"></div></td>
                             </tr>
                             <tr>
                                 <td>匹配网址：</td>
                                 <td>
                                     {{pullDriver.match_1}}
                                     <div class="be-mt-50" v-if="pullDriver.match_2 !== ''">
                                         <br>{{pullDriver.match_2}}
                                     </div>
                                     <div class="be-mt-50" v-if="pullDriver.match_2 !== ''">
                                         <br>{{pullDriver.match_2}}
                                     </div>
                                 </td>
                             </tr>
                             <tr>
                                 <td>起始网址：</td>
                                 <td>{{pullDriver.start_page}}</td>
                             </tr>
                             <tr>
                                 <td>获取下一页脚本：</td>
                                 <td>
                                     <?php
                                     $driver = new \Be\AdminPlugin\Detail\Item\DetailItemCode([
                                         'name' => 'get_next_page_script',
                                         'language' => 'javascript',
                                         'value' => '',
                                     ]);
                                     echo $driver->getHtml();

                                     $uiItems->add($driver);
                                     ?>
                                 </td>
                             </tr>
                             <tr>
                                 <td>获取页面链接脚本：</td>
                                 <td>
                                     <?php
                                     $driver = new \Be\AdminPlugin\Detail\Item\DetailItemCode([
                                         'name' => 'get_links_script',
                                         'language' => 'javascript',
                                         'value' => '',
                                     ]);
                                     echo $driver->getHtml();

                                     $uiItems->add($driver);
                                     ?>
                                 </td>
                             </tr>
                             <tr>
                                 <td>创建时间：</td>
                                 <td>{{pullDriver.create_time}}</td>
                             </tr>
                             <tr>
                                 <td>更新时间：</td>
                                 <td>{{pullDriver.update_time}}</td>
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
                pullDrivers: <?php echo json_encode($this->pullDrivers); ?>,
                pullDriver: false,

                loading: false,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                toggle: function (pullDriver) {
                    this.pullDriver = pullDriver;
                    this.detailItems.get_next_page_script.codeMirror.setValue(pullDriver.get_next_page_script);
                    this.detailItems.get_links_script.codeMirror.setValue(pullDriver.get_links_script);
                },
                save: function () {
                    if (this.pullDriver === false) {
                        this.$message.error("请选择一个采集器！");
                        return;
                    }

                    let url = "<?php echo beAdminUrl('Monkey.PullTask.create'); ?>";
                    url += (url.indexOf("?") === -1 ? '?' : '&');
                    url += "pull_driver_id=" + this.pullDriver.id;
                    window.location.href = url;
                },
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