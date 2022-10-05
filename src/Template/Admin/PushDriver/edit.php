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


        .field-item-header {
            color: #666;
            background-color: #EBEEF5;
            height: 3rem;
            line-height: 3rem;
            margin-bottom: .5rem;
        }

        .field-item {
            background-color: #fff;
            border-bottom: #EBEEF5 1px solid;
            padding-top: .5rem;
            padding-bottom: .5rem;
            margin-bottom: 2px;
        }

        .field-item-drag-icon {
            width: 40px;
            text-align: center;
        }

        .field-item-drag-icon i {
            color: #ccc;
            font-size: 20px;
            cursor: move;
        }

        .field-item-drag-icon i:hover {
            color: #409EFF;
        }

        .field-item-name {
            width: 120px;
            overflow: hidden;
        }

        .field-item-label{
            width: 120px;
            overflow: hidden;
        }

        .field-item-op {
            width: 40px;
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
            $formData['id'] = ($this->pushDriver ? $this->pushDriver->id : '');
            ?>

            <div class="be-row">
                <div class="be-col-24 be-lg-col">

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
                            <div class="be-col-auto be-lh-250">发布网址：</div>
                            <div class="be-col">
                                <el-form-item prop="url">
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
                        <div class="be-row be-mt-100">
                            <div class="be-col-24 be-xxl-col-auto">
                                <div class="be-row field-item-header">
                                    <div class="be-col-auto">
                                        <div class="field-item-drag-icon">
                                        </div>
                                    </div>
                                    <div class="be-col-auto">
                                        <div class="field-item-name">
                                            名称
                                        </div>
                                    </div>
                                    <div class="be-col-auto">
                                        <div class="field-item-op">
                                            操作
                                        </div>
                                    </div>
                                </div>

                                <draggable
                                        v-model="formData.headers"
                                        ghost-class="field-item-ghost"
                                        chosen-class="field-item-chosen"
                                        drag-class="field-item-drag"
                                        handle=".field-item-drag-icon"
                                        force-fallback="true"
                                        animation="100">
                                    <transition-group>
                                        <div class="be-row field-item" v-for="header, headerIndex in formData.headers" :key="header.name">
                                            <div class="be-col-auto">
                                                <div class="field-item-drag-icon">
                                                    <i class="el-icon-rank"></i>
                                                </div>
                                            </div>
                                            <div class="be-col-auto">
                                                <div class="field-item-name">
                                                    <el-link type="primary" @click="editHeader(header)">{{header.name}}</el-link>
                                                </div>
                                            </div>
                                            <div class="be-col-auto">
                                                <div class="field-item-op">
                                                    <el-link type="danger" icon="el-icon-delete" @click="deleteHeader(header)"></el-link>
                                                </div>
                                            </div>
                                        </div>
                                    </transition-group>
                                </draggable>

                                <el-button class="be-mt-100" size="small" type="primary" @click="addHeader">新增请求头</el-button>
                            </div>

                            <div class="be-col-24 be-xxl-col-auto">
                                <div class="be-pl-200 be-pt-200"></div>
                            </div>

                            <div class="be-col-24 be-xxl-col">
                                <div v-show="headerForm">

                                    <div class="be-row">
                                        <div class="be-col-auto be-lh-250">名称：</div>
                                        <div class="be-col">
                                            <el-input
                                                    type="text"
                                                    placeholder="请输入名称"
                                                    v-model = "formData.header_name"
                                                    size="medium"
                                                    maxlength="60"
                                                    show-word-limit>
                                            </el-input>
                                            <?php
                                            $formData['header_name'] = '';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="be-row be-mt-100">
                                        <div class="be-col-auto be-lh-250">值：</div>
                                        <div class="be-col">
                                            <el-input
                                                    type="text"
                                                    placeholder="请输入值"
                                                    v-model = "formData.header_value"
                                                    size="medium">
                                            </el-input>
                                            <?php
                                            $formData['header_value'] = '';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="be-mt-150 be-ta-right">
                                        <el-button size="small" type="primary" :disabled="formData.header_name==='' || formData.header_value===''" @click="saveHeader">确定</el-button>
                                        <el-button size="small" type="danger" @click="headerForm = false;">取消</el-button>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <?php
                        if ($this->pushDriver) {
                            $formData['headers'] = $this->pushDriver->headers;
                        } else {
                            $formData['headers'] = [];
                        }
                        ?>


                        <div class="be-mt-200 be-pb-50 be-bb-eee"><span class="be-c-red">*</span> 发布字段：</div>
                        <div class="be-row be-mt-100">
                            <div class="be-col-24 be-xxl-col-auto">
                                <div class="be-row field-item-header">
                                    <div class="be-col-auto">
                                        <div class="field-item-drag-icon">
                                        </div>
                                    </div>
                                    <div class="be-col-auto">
                                        <div class="field-item-name">
                                            名称
                                        </div>
                                    </div>
                                    <div class="be-col-auto">
                                        <div class="field-item-label">
                                            标签
                                        </div>
                                    </div>
                                    <div class="be-col-auto">
                                        <div class="field-item-op">
                                            操作
                                        </div>
                                    </div>
                                </div>

                                <draggable
                                        v-model="formData.fields"
                                        ghost-class="field-item-ghost"
                                        chosen-class="field-item-chosen"
                                        drag-class="field-item-drag"
                                        handle=".field-item-drag-icon"
                                        force-fallback="true"
                                        animation="100">
                                    <transition-group>
                                        <div class="be-row field-item" v-for="field, fieldIndex in formData.fields" :key="field.name">
                                            <div class="be-col-auto">
                                                <div class="field-item-drag-icon">
                                                    <i class="el-icon-rank"></i>
                                                </div>
                                            </div>
                                            <div class="be-col-auto">
                                                <div class="field-item-name">
                                                    <el-link type="primary" @click="editField(field)">{{field.name}}</el-link>
                                                </div>
                                            </div>
                                            <div class="be-col-auto">
                                                <div class="field-item-label">
                                                    {{field.label}}
                                                </div>
                                            </div>
                                            <div class="be-col-auto">
                                                <div class="field-item-op">
                                                    <el-link type="danger" icon="el-icon-delete" @click="deleteField(field)"></el-link>
                                                </div>
                                            </div>
                                        </div>
                                    </transition-group>
                                </draggable>

                                <el-button class="be-mt-100" size="small" type="primary" @click="addField">新增字段</el-button>
                            </div>

                            <div class="be-col-24 be-xxl-col-auto">
                                <div class="be-pl-200 be-pt-200"></div>
                            </div>

                            <div class="be-col-24 be-xxl-col">
                                <div v-show="fieldForm">

                                    <div class="be-row">
                                        <div class="be-col-auto be-lh-250">名称：</div>
                                        <div class="be-col">
                                            <el-input
                                                    type="text"
                                                    placeholder="请输入名称"
                                                    v-model = "formData.field_name"
                                                    size="medium"
                                                    maxlength="60"
                                                    show-word-limit>
                                            </el-input>
                                            <?php
                                            $formData['field_name'] = '';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="be-row be-mt-100">
                                        <div class="be-col-auto be-lh-250">标签：</div>
                                        <div class="be-col">
                                            <el-input
                                                    type="text"
                                                    placeholder="请输入标签"
                                                    v-model = "formData.field_label"
                                                    size="medium"
                                                    maxlength="60"
                                                    show-word-limit>
                                            </el-input>
                                            <?php
                                            $formData['field_label'] = '';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="be-row be-mt-100">
                                        <div class="be-col-auto be-lh-250">默认值：</div>
                                        <div class="be-col">
                                            <el-input
                                                    type="text"
                                                    placeholder="请输入默认值"
                                                    v-model = "formData.field_default"
                                                    size="medium">
                                            </el-input>
                                            <?php
                                            $formData['field_default'] = '';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="be-row be-mt-100">
                                        <div class="be-col-auto be-lh-250">是否必填：</div>
                                        <div class="be-col be-lh-250">
                                            <el-switch v-model.number="formData.field_required" :active-value="1" :inactive-value="0" size="medium"></el-switch>
                                            <?php
                                            $formData['field_required'] = 0;
                                            ?>
                                        </div>
                                    </div>

                                    <div class="be-mt-150 be-ta-right">
                                        <el-button size="small" type="primary" :disabled="formData.field_name==='' || formData.field_label===''" @click="saveField">确定</el-button>
                                        <el-button size="small" type="danger" @click="fieldForm = false;">取消</el-button>
                                    </div>
                                </div>

                            </div>
                        </div>
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
                                        <el-form-item prop="ordering">
                                            <el-input-number v-model="formData.interval"></el-input-number>
                                        </el-form-item>
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
                            <tr>
                                <td><span class="be-c-red">*</span> 版本号：</td>
                                <td>
                                    <el-form-item prop="version">
                                        <el-form-item prop="version">
                                            <el-input v-model="formData.version">
                                                <template slot="prepend">v</template>
                                            </el-input>
                                        </el-form-item>
                                    </el-form-item>
                                    <?php
                                    $formData['version'] = ($this->pushDriver ? $this->pushDriver->version : '1.0.0');
                                    ?>
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

                headerForm: false,
                fieldForm: false,

                header: false,
                field: false,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                addHeader() {
                    this.header = false;
                    this.formData.header_name = "";
                    this.formData.header_value = "";

                    this.headerForm = true;
                },
                editHeader(header) {
                    this.header = header;
                    this.formData.header_name = header.name;
                    this.formData.header_value = header.value;

                    this.headerForm = true;
                },
                saveHeader() {
                    if (this.header) {
                        this.header.name = this.formData.header_name;
                        this.header.value = this.formData.header_value;
                    } else {
                        this.formData.headers.push({
                            name: this.formData.header_name,
                            value: this.formData.header_value,
                        });
                    }

                    console.log(this.header);
                    console.log(this.formData);

                    this.header = false;
                    this.headerForm = false;
                },
                deleteHeader(header) {
                    let _this = this;
                    this.$confirm("确认要删除请求头（" + header.name + "）么？", "操作确认？", {
                        confirmButtonText: "确定",
                        cancelButtonText: "取消",
                        type: "warning"
                    }).then(function(){
                        _this.formData.headers.splice(_this.formData.headers.indexOf(header), 1);
                    }).catch(function(){});
                },
                addField() {
                    this.field = false;
                    this.formData.field_name = "";
                    this.formData.field_label = "";
                    this.formData.field_default = "";
                    this.formData.field_required = 0;

                    this.fieldForm = true;
                },
                editField(field) {
                    this.field = field;
                    this.formData.field_name = field.name;
                    this.formData.field_label = field.label;
                    this.formData.field_default = field.default;
                    this.formData.field_required = field.required;

                    this.fieldForm = true;
                },
                saveField() {
                    if (this.field) {
                        this.field.name = this.formData.field_name;
                        this.field.label = this.formData.field_label;
                        this.field.default = this.formData.field_default;
                        this.field.required = this.formData.field_required;
                    } else {
                        this.formData.fields.push({
                            name: this.formData.field_name,
                            label: this.formData.field_label,
                            default: this.formData.field_default,
                            required: this.formData.field_required,
                        });
                    }

                    this.field = false;
                    this.fieldForm = false;
                },
                deleteField(field) {
                    let _this = this;
                    this.$confirm("确认要删除发布字段（" + field.name + "）么？", "操作确认？", {
                        confirmButtonText: "确定",
                        cancelButtonText: "取消",
                        type: "warning"
                    }).then(function(){
                        _this.formData.fields.splice(_this.formData.fields.indexOf(field), 1);
                    }).catch(function(){});
                },
                save: function (command) {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Monkey.PushDriver.' . ($this->pushDriver ? 'edit' : 'create')); ?>", {
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