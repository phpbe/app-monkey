<be-head>
    <?php
    $appSystemWwwUrl = \Be\Be::getProperty('App.System')->getWwwUrl();
    ?>
    <script src="<?php echo $appSystemWwwUrl; ?>/lib/sortable/sortable.min.js"></script>
    <script src="<?php echo $appSystemWwwUrl; ?>/lib/vuedraggable/vuedraggable.umd.min.js"></script>

    <style>
        .el-form-item {
            margin-bottom: inherit;
        }

        .el-form-item__content {
            line-height: inherit;
        }

        .monkey-push-task-form-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .monkey-push-task-form-table td {
            padding: .4rem 0;
            vertical-align: middle;
        }
    </style>
</be-head>


<be-north>
    <div class="be-north" id="be-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Monkey.PushTask.pushTasks'); ?>">
                        返回发布任务列表
                    </el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                    <el-dropdown type="primary" size="medium" split-button :disabled="loading" @click="vueCenter.save('')" @command="save">
                        保存
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="stay">保存并继续编辑</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </div>
    <script>
        let vueNorth = new Vue({
            el: '#be-north',
            data: {
                loading: false,
            },
            methods: {
                save: function (command) {
                    vueCenter.save(command)
                }
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
        <el-form ref="formRef" :model="formData" size="medium" class="be-mb-400">
            <?php
            $formData['id'] = ($this->pushTask ? $this->pushTask->id : '');
            $formData['push_driver_id'] = $this->pushDriver->id;
            $formData['pull_driver_id'] = $this->pullDriver->id;
            ?>

            <div class="be-row">
                <div class="be-col-24 be-xl-col">

                    <div class="be-p-150 be-bc-fff">

                        <div class="be-row">
                            <div class="be-col-auto be-lh-250"><span class="be-c-red">*</span> 名称：</div>
                            <div class="be-col">
                                <el-form-item prop="name" :rules="[{required: true, message: '请输入名称', trigger: 'change' }]">
                                    <el-input
                                            type="text"
                                            placeholder="请输入名称"
                                            v-model="formData.name"
                                            maxlength="60"
                                            show-word-limit>
                                    </el-input>
                                </el-form-item>
                                <?php $formData['name'] = ($this->pushTask ? $this->pushTask->name :  ($this->pullDriver->name . ' 发布到 ' . $this->pushDriver->name)); ?>
                            </div>
                        </div>



                        <div class="be-row be-mt-100">
                            <div class="be-col-auto be-lh-250"><span class="be-c-red">*</span> 发布网址：</div>
                            <div class="be-col">
                                <el-form-item prop="url" :rules="[{required: true, message: '请输入发布网址', trigger: 'change' }]">
                                    <el-input
                                            type="text"
                                            placeholder="请输入发布网址"
                                            v-model="formData.url"
                                            maxlength="300"
                                            show-word-limit>
                                    </el-input>
                                </el-form-item>
                                <?php $formData['url'] = ($this->pushTask ? $this->pushTask->url :  $this->pushDriver->url); ?>
                            </div>
                        </div>

                        <?php
                        if ($this->pushTask) {
                            $formData['headers'] = $this->pushTask->headers;
                        } else {
                            $formData['headers'] = $this->pushDriver->headers;
                        }
                        if (count($formData['headers']) > 0) {
                            ?>
                            <div class="be-mt-200 be-pb-50 be-bb-ddd"><span class="be-c-red">*</span> 请求头：</div>
                            <div class="be-row be-mt-100" v-for="header, headerIndex in formData.headers" :key="header.name">
                                <div class="be-col-auto be-lh-250">
                                    {{header.name}}：
                                </div>
                                <div class="be-col">
                                    <div class="be-pl-100">
                                        <el-input type="text" placeholder="请输入值" v-model="header.value" size="medium"></el-input>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>

                        <div class="be-mt-200 be-pb-50 be-bb-ddd"><span class="be-c-red">*</span> 发布字段：</div>
                        <div class="be-row be-mt-100 be-pb-50 be-bb-eee">
                            <div class="be-col-auto">
                                <div class="be-px-100">
                                    <el-checkbox @change="toggleFields"></el-checkbox>
                                </div>
                            </div>

                            <div class="be-col-auto">
                                <div class="be-ta-center" style="width: 200px">字段名</div>
                            </div>

                            <div class="be-col-auto">
                                <div class="be-px-100"></div>
                            </div>

                            <div class="be-col">
                                取值
                            </div>
                        </div>


                        <div class="be-row be-mt-100 be-pb-100 be-bb-eee" v-for="field in formData.fields">
                            <div class="be-col-auto">
                                <div class="be-px-100 be-pt-50">
                                    <el-checkbox :disabled="field.required === 1"  v-model.number="field.is_enable" :true-label="1" :false-label="0"></el-checkbox>
                                </div>
                            </div>

                            <div class="be-col-auto">
                                <el-button :disabled="field.is_enable === 0" type="primary" style="width:200px; text-align:center;" class="be-t-ellipsis">{{field.label}}（{{field.name}}）
                                </el-button>
                            </div>

                            <div class="be-col-auto">
                                <div class="be-px-50 be-pt-50 be-c-999">
                                    <i class="el-icon-right"></i>
                                </div>
                            </div>

                            <div class="be-col">
                                <div class="be-row be-mt:50">
                                    <div class="be-col-auto be-lh-250">
                                        <el-radio :disabled="field.is_enable === 0" v-model="field.value_type" label="pull_driver_field">采集器字段</el-radio>
                                    </div>
                                    <div class="be-col-auto be-lh-250" v-if="field.value_type === 'pull_driver_field'">：</div>
                                    <div class="be-col-auto" v-if="field.value_type === 'pull_driver_field'">
                                        <el-select :disabled="field.is_enable === 0" v-model="field.value_pull_driver_field" size="medium" placeholder="请选择" filterable>
                                            <el-option
                                                    v-for="pullDriverField in pullDriver.fields"
                                                    :key="pullDriverField.name"
                                                    :label="pullDriverField.name"
                                                    :value="pullDriverField.name">
                                            </el-option>
                                        </el-select>
                                    </div>
                                </div>

                                <div class="be-row be-mt:50 be-lh-250" v-if="field.value_default !== ''">
                                    <div class="be-col-auto">
                                        <el-radio :disabled="field.is_enable === 0" v-model="field.value_type" label="default">默认值</el-radio>
                                    </div>
                                    <div class="be-col-auto" v-if="field.value_type === 'default'">：</div>
                                    <div class="be-col-auto" v-if="field.value_type === 'default'">
                                        <span :class="{'be-c-999' : field.is_enable === 0}">{{field.value_default}}</span>
                                    </div>
                                </div>

                                <div class="be-row be-mt:50">
                                    <div class="be-col-auto be-lh-250">
                                        <el-radio :disabled="field.is_enable === 0" v-model="field.value_type" label="custom">自定义</el-radio>
                                    </div>
                                    <div class="be-col-auto be-lh-250" v-if="field.value_type === 'custom'">：</div>
                                    <div class="be-col-auto" v-if="field.value_type === 'custom'">
                                        <el-input :disabled="field.is_enable === 0" v-model="field.value_custom" size="medium" :value="field.default" placeholder="请输入自定义值"></el-input>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($this->pushTask) {

                            foreach ($this->pushTask->fields as &$pushTaskField) {
                                if ($pushTaskField['value_type'] === 'default') {
                                    foreach ($this->pushDriver->fields as $pushDriverField) {
                                        if ($pushTaskField['name'] === $pushDriverField['name']) {
                                            $pushTaskField['value_default'] = $pushDriverField['default'];
                                            break;
                                        }
                                    }
                                }
                            }
                            unset($pushTaskField);

                            $formData['fields'] = $this->pushTask->fields;

                        } else {
                            $fields = [];
                            foreach ($this->pushDriver->fields as $pushDriverField) {
                                $field = [];
                                $field['is_enable'] = 1;
                                $field['name'] = $pushDriverField['name'];
                                $field['label'] = $pushDriverField['label'];
                                $field['default'] = $pushDriverField['default'];
                                $field['required'] = $pushDriverField['required'];

                                if ($pushDriverField['default'] === '') {
                                    $field['value_type'] = 'custom';
                                } else {
                                    $field['value_type'] = 'default';
                                }
                                $field['value_pull_driver_field'] = '';

                                $field['value_default'] = $pushDriverField['default'];
                                $field['value_custom'] = '';

                                foreach ($this->pullDriver->fields as $pullDriverField) {
                                    if ($pullDriverField['name'] === $pushDriverField['name'] || $pullDriverField['name'] === $pushDriverField['label']) {
                                        $field['value_type'] = 'pull_driver_field';
                                        $field['value_pull_driver_field'] = $pullDriverField['name'];
                                        break;
                                    }
                                }
                                $fields[] = $field;
                            }
                            $formData['fields'] = $fields;
                        }
                        ?>

                    </div>
                </div>
                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-mt-150 be-pl-150"></div>
                </div>
                <div class="be-col-24 be-xl-col-auto">

                    <div class="be-p-150 be-bc-fff" style="max-height: 400px;">
                        <table class="monkey-push-task-form-table">
                            <tr>
                                <td>是否启用：</td>
                                <td>
                                    <el-form-item prop="is_enable">
                                        <el-switch v-model.number="formData.is_enable" :active-value="1" :inactive-value="0"></el-switch>
                                    </el-form-item>
                                    <?php $formData['is_enable'] = ($this->pushTask ? $this->pushTask->is_enable : 1); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>间隔时间（毫秒）：</td>
                                <td>
                                    <el-form-item prop="interval">
                                        <el-form-item prop="ordering">
                                            <el-input-number v-model="formData.interval"></el-input-number>
                                        </el-form-item>
                                    </el-form-item>
                                    <?php $formData['interval'] = ($this->pushTask ? $this->pushTask->interval : $this->pushDriver->interval); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>排序：</td>
                                <td>
                                    <el-form-item prop="ordering">
                                        <el-input-number v-model="formData.ordering"></el-input-number>
                                    </el-form-item>
                                    <?php $formData['ordering'] = ($this->pushTask ? $this->pushTask->ordering : 0); ?>
                                </td>
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
                loading: false,

                pushDriver: <?php echo json_encode($this->pushDriver); ?>,
                pullDriver: <?php echo json_encode($this->pullDriver); ?>,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                toggleFields: function (val) {
                    for(field of this.formData.fields) {
                        if (field.required) {
                            field.is_enable = 1;
                        } else {
                            field.is_enable = val ? 1 : 0;
                        }
                    }
                },
                save: function (command) {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Monkey.PushTask.' . ($this->pushTask ? 'edit' : 'create'), ['push_driver_id' => $this->pushDriver->id]); ?>", {
                                formData: _this.formData
                            }).then(function (response) {
                                _this.loading = false;
                                vueNorth.loading = false;
                                //console.log(response);
                                if (response.status === 200) {
                                    var responseData = response.data;
                                    if (responseData.success) {
                                        _this.$message.success(responseData.message);
                                        if (command === 'stay') {
                                            _this.formData.id = responseData.ruld.id;
                                        } else {
                                            setTimeout(function () {
                                                window.onbeforeunload = null;
                                                window.location.href = "<?php echo beAdminUrl('Monkey.PushTask.pushTasks'); ?>";
                                            }, 1000);
                                        }
                                    } else {
                                        if (responseData.message) {
                                            _this.$message.error(responseData.message);
                                        } else {
                                            _this.$message.error("服务器返回数据异常！");
                                        }
                                    }
                                }
                            }).catch(function (error) {
                                _this.loading = false;
                                vueNorth.loading = false;
                                _this.$message.error(error);
                            });
                        } else {
                            return false;
                        }
                    });
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
            $uiItems->setVueHook('mounted', 'window.onbeforeunload = function(e) {e = e || window.event; if (e) { e.returnValue = ""; } return ""; };');
            echo $uiItems->getVueHooks();
            ?>
        });

    </script>

</be-page-content>