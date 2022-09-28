<?php

namespace Be\App\Monkey\Service\Admin;

use Be\App\ServiceException;
use Be\Be;

class Rule
{

    /**
     * 获取规则列表
     *
     * @return array
     */
    public function getRules(): array
    {
        $sql = 'SELECT * FROM monkey_rule WHERE is_delete = 0 ORDER BY `ordering` DESC';
        $rules = Be::getDb()->getObjects($sql);
        return $rules;
    }

    /**
     * 获取规则
     *
     * @param string $ruleId
     * @return object
     */
    public function getRule(string $ruleId): object
    {
        $db = Be::getDb();

        $sql = 'SELECT * FROM monkey_rule WHERE id=? AND is_delete = 0';
        $rule = $db->getObject($sql, [$ruleId]);
        if (!$rule) {
            throw new ServiceException('规则（# ' . $ruleId . '）不存在！');
        }

        $rule->ordering = (int)$rule->ordering;
        $rule->is_enable = (int)$rule->is_enable;
        $rule->is_delete = (int)$rule->is_delete;

        $sql = 'SELECT * FROM monkey_rule_field WHERE rule_id=? ORDER BY `ordering` ASC';
        $fields = $db->getObjects($sql, [$ruleId]);
        $rule->fields = $fields;

        return $rule;
    }

    /**
     * 获取规则键值对
     *
     * @return array
     */
    public function getRuleKeyValues(): array
    {
        $sql = 'SELECT id, `name` FROM monkey_rule WHERE is_delete = 0 ORDER BY `ordering` DESC';
        return Be::getDb()->getKeyValues($sql);
    }


    /**
     * 编辑规则
     *
     * @param array $data 规则数据
     * @return object
     */
    public function edit(array $data): object
    {
        $db = Be::getDb();

        $isNew = true;
        $ruleId = null;
        if (isset($data['id']) && is_string($data['id']) && $data['id'] !== '') {
            $isNew = false;
            $ruleId = $data['id'];
        }

        $tupleRule = Be::getTuple('monkey_rule');
        if (!$isNew) {
            try {
                $tupleRule->load($ruleId);
            } catch (\Throwable $t) {
                throw new ServiceException('规则（# ' . $ruleId . '）不存在！');
            }

            if ($tupleRule->is_delete === 1) {
                throw new ServiceException('规则（# ' . $ruleId . '）不存在！');
            }
        }

        if (!isset($data['name']) || !is_string($data['name'])) {
            throw new ServiceException('规则名称未填写！');
        }

        if (!isset($data['description']) || !is_string($data['description'])) {
            $data['description'] = '';
        }

        if (!isset($data['match_1']) || !is_string($data['match_1'])) {
            throw new ServiceException('匹配网址1未填写！');
        }

        if (!isset($data['match_2']) || !is_string($data['match_2'])) {
            $data['match_2'] = '';
        }

        if (!isset($data['match_3']) || !is_string($data['match_3'])) {
            $data['match_3'] = '';
        }

        if (!isset($data['start_page']) || !is_string($data['start_page'])) {
            throw new ServiceException('起始页未填写！');
        }

        if (!isset($data['get_next_page_script']) || !is_string($data['get_next_page_script'])) {
            throw new ServiceException('获取下一页脚本未填写！');
        }

        if (!isset($data['get_links_script']) || !is_string($data['get_links_script'])) {
            throw new ServiceException('获取页面链接脚本未填写！');
        }

        if (!isset($data['interval']) || !is_numeric($data['interval'])) {
            $data['interval'] = 1000;
        }

        $data['interval'] = (int)$data['interval'];

        if ($data['interval'] <= 0) {
            $data['interval'] = 1000;
        }

        if (!isset($data['version']) || !is_string($data['version'])) {
            $data['version'] = '';
        }

        if (!isset($data['ordering']) || !is_numeric($data['ordering'])) {
            $data['ordering'] = 0;
        }

        $data['ordering'] = (int)$data['ordering'];

        if (!isset($data['is_enable']) || !is_numeric($data['is_enable'])) {
            $data['is_enable'] = 0;
        }

        if (!isset($data['fields']) || !is_array($data['fields'])) {
            throw new ServiceException('采集字段缺失！');
        }

        $isTitleFields = 0;

        $i = 0;
        foreach ($data['fields'] as &$field) {
            $i++;
            if (!isset($field['name']) || !is_string($field['name'])) {
                throw new ServiceException('第' . $i . '个采集字段名称缺失！');
            }

            $field['name'] = trim($field['name']);

            if ($field['name'] === '') {
                throw new ServiceException('第' . $i . '个采集字段名称未填写！');
            }

            if (!isset($field['script']) || !is_string($field['script'])) {
                throw new ServiceException('第' . $i . '个采集字段脚本缺失！');
            }

            $field['script'] = trim($field['script']);

            if ($field['script'] === '') {
                throw new ServiceException('第' . $i . '个采集字段脚本未填写！');
            }

            if (!isset($field['is_title']) || !is_numeric($field['is_title'])) {
                $field['is_title'] = 0;
            }

            if (!in_array($field['is_title'], [0, 1])) {
                $field['is_title'] = 0;
            }

            if ($field['is_title'] === 1) {
                $isTitleFields++;
            }
        }
        unset($field);

        if ($isTitleFields !== 1) {
            throw new ServiceException('采集字段必须且仅设置一个标题字段！');
        }

        $db->startTransaction();
        try {
            $now = date('Y-m-d H:i:s');
            $tupleRule->name = $data['name'];
            $tupleRule->description = $data['description'];
            $tupleRule->match_1 = $data['match_1'];
            $tupleRule->match_2 = $data['match_2'];
            $tupleRule->match_3 = $data['match_3'];
            $tupleRule->start_page = $data['start_page'];
            $tupleRule->get_next_page_script = $data['get_next_page_script'];
            $tupleRule->get_links_script = $data['get_links_script'];
            $tupleRule->interval = $data['interval'];
            $tupleRule->version = $data['version'];
            $tupleRule->ordering = $data['ordering'];
            $tupleRule->is_enable = $data['is_enable'];
            $tupleRule->update_time = $now;
            if ($isNew) {
                $tupleRule->is_delete = 0;
                $tupleRule->create_time = $now;
                $tupleRule->insert();
            } else {
                $tupleRule->update();
            }

            // 采集字段
            if ($isNew) {
                $ordering = 0;
                foreach ($data['fields'] as $field) {
                    $tupleRuleField = Be::getTuple('monkey_rule_field');
                    $tupleRuleField->rule_id = $tupleRule->id;
                    $tupleRuleField->name = $field['name'];
                    $tupleRuleField->script = $field['script'];
                    $tupleRuleField->is_title = $field['is_title'];
                    $tupleRuleField->ordering = $ordering++;
                    $tupleRuleField->insert();
                }
            } else {
                $keepIds = [];
                foreach ($data['fields'] as $field) {
                    if (isset($field['id']) && $field['id'] !== '') {
                        $keepIds[] = $field['id'];
                    }
                }

                if (count($keepIds) > 0) {
                    Be::getTable('monkey_rule_field')
                        ->where('rule_id', $ruleId)
                        ->where('id', 'NOT IN', $keepIds)
                        ->delete();
                } else {
                    Be::getTable('monkey_rule_field')
                        ->where('rule_id', $ruleId)
                        ->delete();
                }

                $ordering = 0;
                foreach ($data['fields'] as $field) {
                    $tupleRuleField = Be::getTuple('monkey_rule_field');
                    if (isset($field['id']) && $field['id'] !== '') {
                        try {
                            $tupleRuleField->loadBy([
                                'id' => $field['id'],
                                'rule_id' => $tupleRule->id,
                            ]);
                        } catch (\Throwable $t) {
                            throw new ServiceException('采集规则（# ' . $ruleId . ' ' . $tupleRule->name . '）下的采集字段（# ' . $field['id'] . '）不存在！');
                        }
                    }

                    $tupleRuleField->rule_id = $tupleRule->id;
                    $tupleRuleField->name = $field['name'];
                    $tupleRuleField->script = $field['script'];
                    $tupleRuleField->is_title = $field['is_title'];
                    $tupleRuleField->ordering = $ordering++;

                    if (!isset($field['id']) || $field['id'] === '') {
                        $tupleRuleField->create_time = $now;
                    }

                    $tupleRuleField->update_time = $now;
                    $tupleRuleField->save();
                }
            }

            $db->commit();

        } catch (\Throwable $t) {
            $db->rollback();
            Be::getLog()->error($t);

            throw new ServiceException(($isNew ? '新建' : '编辑') . '规则发生异常！');
        }

        return $tupleRule->toObject();
    }


}
