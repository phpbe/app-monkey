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

        .monkey-push-driver-form-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .monkey-push-driver-form-table td {
            padding: .4rem 0;
            vertical-align: middle;
        }


        .be-page-content .field-item-header {
            color: #666;
            background-color: #EBEEF5;
            height: 3rem;
            line-height: 3rem;
            margin-bottom: .5rem;
        }

        .be-page-content  .field-item {
            background-color: #fff;
            border-bottom: #EBEEF5 1px solid;
            padding-top: .5rem;
            padding-bottom: .5rem;
            margin-bottom: 2px;
        }

        .be-page-content  .field-item-op {
            width: 40px;
            line-height: 2.5rem;
            text-align: center;
        }
    </style>
</be-head>


<be-north>
    <div class="be-north" id="be-north">
        <div class="be-row">
            <div class="be-col">
                <div style="padding: 1.25rem 0 0 2rem;">
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Monkey.PushDriver.pushDrivers'); ?>">
                        返回发布器列表
                    </el-link>
                </div>
            </div>
            <div class="be-col-auto">
                <div style="padding: .75rem 2rem 0 0;">
                    <el-button size="medium" :disabled="loading" @click="vueCenter.cancel();">取消</el-button>
                    <el-button type="success" size="medium" :disabled="loading" @click="vueCenter.save('stay');">仅保存</el-button>
                    <el-button type="primary" size="medium" :disabled="loading" @click="vueCenter.save('');">保存并返回</el-button>
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
        <el-form ref="formRef" :model="formData" size="medium" class="be-mb-400">
            <?php
            $formData['id'] = ($this->pushDriver ? $this->pushDriver->id : '');
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
                                <?php $formData['name'] = ($this->pushDriver ? $this->pushDriver->name : ''); ?>
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
                                <?php $formData['url'] = ($this->pushDriver ? $this->pushDriver->url : ''); ?>
                            </div>
                        </div>


                        <div class="be-mt-200 be-pb-50 be-bb-eee">请求头：</div>
                        <div class="be-row be-mt-100 field-item-header">
                            <div class="be-col">
                                <div class="be-pl-100">名称</div>
                            </div>
                            <div class="be-col-auto">
                                <div class="be-pl-100"></div>
                            </div>
                            <div class="be-col">
                                值
                            </div>
                            <div class="be-col-auto">
                                <div class="field-item-op">
                                    操作
                                </div>
                            </div>
                        </div>

                        <div class="be-row field-item" v-for="header, headerIndex in formData.headers" :key="headerIndex">
                            <div class="be-col">
                                <el-input
                                        type="text"
                                        placeholder="请输入名称"
                                        v-model = "header.name"
                                        size="medium"
                                        maxlength="300"
                                        show-word-limit>
                                </el-input>
                            </div>
                            <div class="be-col-auto">
                                <div class="be-pl-100"></div>
                            </div>
                            <div class="be-col">
                                <el-input
                                        type="text"
                                        placeholder="请输入值"
                                        v-model = "header.value"
                                        size="medium"
                                        maxlength="600"
                                        show-word-limit>
                                </el-input>
                            </div>
                            <div class="be-col-auto">
                                <div class="field-item-op">
                                    <el-link type="danger" icon="el-icon-delete" @click="deleteHeader(header)"></el-link>
                                </div>
                            </div>
                        </div>

                        <el-button class="be-mt-100" size="small" type="primary" @click="addHeader">新增请求头</el-button>
                        <?php
                        if ($this->pushDriver) {
                            $formData['headers'] = $this->pushDriver->headers;
                        } else {
                            $formData['headers'] = [];
                        }
                        ?>

                        <div class="be-row be-mt-200">
                            <div class="be-col-auto">
                                <span class="be-c-red">*</span> 请求格式：
                            </div>
                            <div class="be-col">
                                <el-radio v-model="formData.format" label="form">FORM 表单</el-radio>
                                <el-radio v-model="formData.format" label="json">JSON 数据</el-radio>
                            </div>
                        </div>
                        <?php $formData['format'] = $this->pushDriver ? $this->pushDriver->format : 'form'; ?>


                        <div class="be-mt-200 be-pb-50 be-bb-ddd"><span class="be-c-red">*</span> 发布字段：</div>
                        <div class="be-row field-item-header">
                            <div class="be-col">
                                <div class="be-pl-100">字段名</div>
                            </div>
                            <div class="be-col-auto">
                                <div class="be-pl-100"></div>
                            </div>
                            <div class="be-col be-ta-center">
                                取值类型
                            </div>
                            <div class="be-col-auto">
                                <div class="be-pl-100"></div>
                            </div>
                            <div class="be-col">
                                采集的内容或自定义
                            </div>
                            <div class="be-col-auto">
                                <div class="field-item-op">
                                    操作
                                </div>
                            </div>
                        </div>

                        <div class="be-row field-item" v-for="field, fieldIndex in formData.fields" :key="fieldIndex">
                            <div class="be-col">
                                <el-input
                                        type="text"
                                        placeholder="请输入字段名"
                                        v-model = "field.name"
                                        size="medium"
                                        maxlength="300"
                                        show-word-limit>
                                </el-input>
                            </div>
                            <div class="be-col-auto">
                                <div class="be-pl-100"></div>
                            </div>
                            <div class="be-col be-ta-center be-lh-250">
                                <el-radio v-model="field.value" label="use">取用</el-radio>
                                <el-radio v-model="field.value" label="custom">自定义</el-radio>
                            </div>
                            <div class="be-col-auto">
                                <div class="be-pl-100"></div>
                            </div>
                            <div class="be-col">
                                <div v-show="field.value === 'use'">
                                    <el-select  v-model="field.value_use" size="medium" placeholder="请选择" filterable>
                                        <el-option
                                                v-for="pullDriverField in pullDriver.fields"
                                                :key="pullDriverField.name"
                                                :label="pullDriverField.name"
                                                :value="pullDriverField.name">
                                        </el-option>
                                    </el-select>
                                </div>
                                <div v-show="field.value === 'custom'">
                                    <el-input
                                            type="text"
                                            placeholder="请输入自定义值"
                                            v-model = "field.value_custom"
                                            size="medium">
                                    </el-input>
                                </div>
                            </div>
                            <div class="be-col-auto">
                                <div class="field-item-op">
                                    <el-link type="danger" icon="el-icon-delete" @click="deleteField(field)"></el-link>
                                </div>
                            </div>
                        </div>

                        <el-button class="be-mt-100" size="small" type="primary" @click="addField">新增字段</el-button>
                        <?php
                        if ($this->pushDriver) {
                            $formData['fields'] = $this->pushDriver->fields;
                        } else {
                            $formData['fields'] = [];
                        }
                        ?>
                    </div>
                </div>

                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-mt-150 be-pl-150"></div>
                </div>
                <div class="be-col-24 be-xl-col-auto">

                    <div class="be-p-150 be-bc-fff" style="max-height: 400px;">
                        <table class="monkey-push-driver-form-table">
                            <tr>
                                <td>是否启用：</td>
                                <td>
                                    <el-form-item prop="is_enable">
                                        <el-switch v-model.number="formData.is_enable" :active-value="1" :inactive-value="0"></el-switch>
                                    </el-form-item>
                                    <?php $formData['is_enable'] = ($this->pushDriver ? $this->pushDriver->is_enable : 1); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>间隔时间（毫秒）：</td>
                                <td>
                                    <el-form-item prop="interval">
                                        <el-input-number v-model="formData.interval"></el-input-number>
                                    </el-form-item>
                                    <?php $formData['interval'] = ($this->pushDriver ? $this->pushDriver->interval : 1000); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>排序：</td>
                                <td>
                                    <el-form-item prop="ordering">
                                        <el-input-number v-model="formData.ordering"></el-input-number>
                                    </el-form-item>
                                    <?php $formData['ordering'] = ($this->pushDriver ? $this->pushDriver->ordering : 0); ?>
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

                pullDriver: <?php echo json_encode($this->pullDriver); ?>,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {

                addHeader: function () {
                    this.formData.headers.push({
                        name: "",
                        value: "",
                    });
                },
                deleteHeader(header) {
                    this.formData.headers.splice(this.formData.headers.indexOf(header), 1);
                },

                addField: function () {
                    this.formData.fields.push({
                        name: "",
                        value: "use",
                        value_use: "",
                        value_custom: "",
                    });
                    this.$forceUpdate();
                },
                deleteField: function (field) {
                    this.formData.fields.splice(this.formData.fields.indexOf(field), 1);
                },

                save: function (command) {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Monkey.PushDriver.' . ($this->pushDriver ? 'edit' : 'create'), ['push_driver_id' => $this->pushDriver->id]); ?>", {
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
                                            _this.formData.id = responseData.pushDriver.id;
                                        } else {
                                            setTimeout(function () {
                                                window.onbeforeunload = null;
                                                window.location.href = "<?php echo beAdminUrl('Monkey.PushDriver.pushDrivers'); ?>";
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
                    window.location.href = "<?php echo beAdminUrl('Monkey.PushDriver.pushDrivers'); ?>";
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