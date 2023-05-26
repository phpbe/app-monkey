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

        .el-tooltip {
            cursor: pointer;
        }

        .el-tooltip:hover {
            color: #409EFF;
        }

        .monkey-pull-driver-form-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
        }

        .monkey-pull-driver-form-table td {
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
            width: 90px;
            overflow: hidden;
        }

        .field-item-is-title {
            width: 90px;
            text-align: center;
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
                    <el-link icon="el-icon-back" href="<?php echo beAdminUrl('Monkey.PullDriver.pullDrivers'); ?>">
                        返回采集器列表
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
            $formData['id'] = ($this->pullDriver ? $this->pullDriver->id : '');
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
                                <?php $formData['name'] = ($this->pullDriver ? $this->pullDriver->name : ''); ?>
                            </div>
                        </div>


                        <div class="be-mt-100">描述：</div>
                        <?php
                        $driver = new \Be\AdminPlugin\Form\Item\FormItemTinymce([
                            'name' => 'description',
                            'ui' => [
                                'form-item' => [
                                    'class' => 'be-mt-50'
                                ],
                            ],
                            'layout' => 'simple',
                        ]);
                        echo $driver->getHtml();

                        $formData['description'] = ($this->pullDriver ? $this->pullDriver->description : '');

                        $uiItems->add($driver);
                        ?>

                    </div>

                </div>
                <div class="be-col-24 be-lg-col-auto">
                    <div class="be-mt-150 be-pl-150"></div>
                </div>
                <div class="be-col-24 be-lg-col">

                    <div class="be-p-150 be-bc-fff">
                        <table class="monkey-pull-driver-form-table">
                            <tr>
                                <td>是否启用：</td>
                                <td>
                                    <el-form-item prop="is_enable">
                                        <el-switch v-model.number="formData.is_enable" :active-value="1" :inactive-value="0"></el-switch>
                                    </el-form-item>
                                    <?php $formData['is_enable'] = ($this->pullDriver ? $this->pullDriver->is_enable : 1); ?>
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
                                    <?php $formData['interval'] = ($this->pullDriver ? $this->pullDriver->interval : 1000); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>排序：</td>
                                <td>
                                    <el-form-item prop="ordering">
                                        <el-input-number v-model="formData.ordering"></el-input-number>
                                    </el-form-item>
                                    <?php $formData['ordering'] = ($this->pullDriver ? $this->pullDriver->ordering : 0); ?>
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
                                    $formData['version'] = ($this->pullDriver ? $this->pullDriver->version : '1.0.0');
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td><span class="be-c-red">*</span> 匹配网址1：</td>
                                <td>
                                    <el-form-item prop="match_1" :rules="[{required: true, message: '请输入匹配网址1', trigger: 'change' }]">
                                        <el-input
                                                type="text"
                                                placeholder="请输入匹配网址1"
                                                v-model="formData.match_1"
                                                maxlength="120"
                                                show-word-limit>
                                        </el-input>
                                    </el-form-item>
                                    <?php $formData['match_1'] = ($this->pullDriver ? $this->pullDriver->match_1 : ''); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>匹配网址2：</td>
                                <td>
                                    <el-form-item prop="match_2">
                                        <el-input
                                                type="text"
                                                placeholder="请输入匹配网址2"
                                                v-model="formData.match_2"
                                                maxlength="120"
                                                show-word-limit>
                                        </el-input>
                                    </el-form-item>
                                    <?php $formData['match_2'] = ($this->pullDriver ? $this->pullDriver->match_2 : ''); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>匹配网址3：</td>
                                <td>
                                    <el-form-item prop="match_3">
                                        <el-input
                                                type="text"
                                                placeholder="请输入匹配网址3"
                                                v-model="formData.match_3"
                                                maxlength="120"
                                                show-word-limit>
                                        </el-input>
                                    </el-form-item>
                                    <?php $formData['match_3'] = ($this->pullDriver ? $this->pullDriver->match_3 : ''); ?>
                                </td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>

            <div class="be-row be-mt-150">
                <div class="be-col-24 be-xl-col">

                    <div class="be-p-150 be-bc-fff">

                        <div class="be-row">
                            <div class="be-col-auto be-lh-250"><span class="be-c-red">*</span> 起始页：</div>
                            <div class="be-col">
                                <el-form-item prop="start_page" :rules="[{required: true, message: '请输入起始页', trigger: 'change' }]">
                                    <el-input
                                            type="text"
                                            placeholder="请输入起始页	"
                                            v-model="formData.start_page"
                                            maxlength="120"
                                            show-word-limit>
                                    </el-input>
                                </el-form-item>
                                <?php $formData['start_page'] = ($this->pullDriver ? $this->pullDriver->start_page : ''); ?>
                            </div>
                        </div>


                        <div class="be-mt-100"><span class="be-c-red">*</span> 获取下一页脚本：</div>
                        <?php
                        $driver = new \Be\AdminPlugin\Form\Item\FormItemCode([
                            'name' => 'get_next_page_script',
                            'language' => 'javascript',
                            'ui' => [
                                'form-item' => [
                                    'class' => 'be-mt-50'
                                ],
                            ],
                            'required' => true,
                        ]);
                        echo $driver->getHtml();

                        $formData['get_next_page_script'] = ($this->pullDriver ? $this->pullDriver->get_next_page_script : '');

                        $uiItems->add($driver);
                        ?>


                        <div class="be-mt-100"><span class="be-c-red">*</span> 获取页面链接脚本：</div>
                        <?php
                        $driver = new \Be\AdminPlugin\Form\Item\FormItemCode([
                            'name' => 'get_links_script',
                            'language' => 'javascript',
                            'ui' => [
                                'form-item' => [
                                    'class' => 'be-mt-50'
                                ],
                            ],
                            'required' => true,
                        ]);
                        echo $driver->getHtml();

                        $formData['get_links_script'] = ($this->pullDriver ? $this->pullDriver->get_links_script : '');

                        $uiItems->add($driver);
                        ?>
                    </div>

                </div>
                <div class="be-col-24 be-xl-col-auto">
                    <div class="be-mt-150 be-pl-150"></div>
                </div>
                <div class="be-col-24 be-xl-col">

                    <div class="be-p-150 be-bc-fff">
                        <div class="be-fs-110">
                            采集字段：
                        </div>

                        <div class="be-row be-mt-100">
                            <div class="be-col-auto">
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
                                        <div class="field-item-is-title">
                                            是否标题字段
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
                                                <div class="field-item-is-title">
                                                    <el-switch v-model.number="field.is_title" :active-value="1" :inactive-value="0" @change="updateFieldsIsTitle(field)"></el-switch>
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

                            <div class="be-col-auto">
                                <div class="be-pl-200"></div>
                            </div>

                            <div class="be-col">
                                <div v-show="fieldForm">

                                    <div class="be-row">
                                        <div class="be-col-auto be-lh-250">字段名称：</div>
                                        <div class="be-col">
                                            <el-input
                                                    type="text"
                                                    placeholder="请输入字段名称"
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

                                    <div class="be-mt-100">
                                        采集脚本：
                                    </div>
                                    <?php
                                    $driver = new \Be\AdminPlugin\Form\Item\FormItemCode([
                                        'name' => 'field_script',
                                        'language' => 'javascript',
                                        'ui' => [
                                            'form-item' => [
                                                'class' => 'be-mt-50'
                                            ],
                                        ],
                                    ]);
                                    echo $driver->getHtml();

                                    $formData['field_script'] = '';

                                    $uiItems->add($driver);
                                    ?>

                                    <div class="be-mt-150 be-ta-right">
                                        <el-button size="small" type="primary" :disabled="formData.field_name===''" @click="saveField">确定</el-button>
                                        <el-button size="small" type="danger" @click="fieldForm = false;">取消</el-button>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <?php
                        if ($this->pullDriver) {
                            $formData['fields'] = $this->pullDriver->fields;
                        } else {
                            $formData['fields'] = [];
                        }
                        ?>

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

                fieldForm: false,

                field: false,

                t: false
                <?php
                echo $uiItems->getVueData();
                ?>
            },
            methods: {
                addField() {
                    this.field = false;
                    this.formData.field_name = "";
                    this.formData.field_script = "";

                    this.formItems.field_script.codeMirror.setValue(this.formData.field_script);

                    this.fieldForm = true;
                },
                editField(field) {
                    this.field = field;
                    this.formData.field_name = field.name;
                    this.formData.field_script = field.script;

                    this.formItems.field_script.codeMirror.setValue(this.formData.field_script);

                    this.fieldForm = true;
                },
                saveField() {
                    this.formData.field_script = this.formItems.field_script.codeMirror.getValue();

                    if (this.field) {
                        this.field.name = this.formData.field_name;
                        this.field.script = this.formData.field_script;
                    } else {
                        this.formData.fields.push({
                            name: this.formData.field_name,
                            script: this.formData.field_script,
                            is_title: 0,
                        });
                    }

                    this.fieldForm = false;
                    this.updateFieldsIsTitle(false);
                },
                deleteField(field) {
                    let _this = this;
                    this.$confirm("确认要删除采集字段（" + field.name + "）么？", "操作确认？", {
                        confirmButtonText: "确定",
                        cancelButtonText: "取消",
                        type: "warning"
                    }).then(function(){
                        _this.formData.fields.splice(_this.formData.fields.indexOf(field), 1);
                        _this.updateFieldsIsTitle(false);
                    }).catch(function(){});
                },
                updateFieldsIsTitle(field) {
                    if (this.formData.fields.length === 0) {
                        return;
                    }

                    let counter = 0;
                    for (let f of this.formData.fields) {
                        if (f.is_title === 1) {
                            counter++;
                        }
                    }

                    if (counter > 1) {
                        if (field === false || field.is_title === 0) {
                            counter = 0;
                            for (let f of this.formData.fields) {
                                if (f.is_title === 1) {
                                    if (counter >= 1) {
                                        f.is_title = 0;
                                    }
                                    counter++;
                                }
                            }
                        } else {
                            for (let f of this.formData.fields) {
                                if (f !== field) {
                                    f.is_title = 0;
                                }
                            }
                        }
                    } else if (counter === 0) {
                        if (field === false) {
                            this.formData.fields[0].is_title = 1;
                        } else {
                            counter = 0;
                            for (let f of this.formData.fields) {
                                if (f !== field && counter === 0) {
                                    f.is_title = 1;
                                    counter++;
                                    break;
                                }
                            }

                            if (counter === 0) {
                                this.formData.fields[0].is_title = 1;
                            }
                        }
                    }
                },
                save: function (command) {
                    let _this = this;
                    this.$refs["formRef"].validate(function (valid) {
                        if (valid) {
                            _this.loading = true;
                            vueNorth.loading = true;
                            _this.$http.post("<?php echo beAdminUrl('Monkey.PullDriver.' . ($this->pullDriver ? 'edit' : 'create')); ?>", {
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
                                            _this.formData.id = responseData.pullDriver.id;
                                        } else {
                                            setTimeout(function () {
                                                window.onbeforeunload = null;
                                                window.location.href = "<?php echo beAdminUrl('Monkey.PullDriver.pullDrivers'); ?>";
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
                    window.location.href = "<?php echo beAdminUrl('Monkey.PullDriver.pullDrivers'); ?>";
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