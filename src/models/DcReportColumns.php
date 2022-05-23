<?php
/**
 * 数据库表 "dc_report_columns" 的模型对象.
 * @property int $id 流水号
 * @property int $report_id 报表
 * @property int $set_id 数据集
 * @property int $col_id 数据集字段
 * @property int $paixu 排序
 */

namespace datacenter\models;

use Yii;

class DcReportColumns extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_report_columns';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['report_id', 'set_id', 'col_id', 'paixu', 'is_frozen', 'user_set_id'], 'safe'],
            [['formula'], 'string', 'max' => 255],
            [['label'], 'string', 'max' => 50],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'label' => Yii::t('datacenter', '标签'),
            'report_id' => Yii::t('datacenter', '报表'),
            'set_id' => Yii::t('datacenter', '数据集'),
            'user_set_id' => Yii::t('datacenter', '用户数据集'),
            'col_id' => Yii::t('datacenter', '数据集字段'),
            'paixu' => Yii::t('datacenter', '排序'),
            'formula' => Yii::t('datacenter', '计算公式'),
            'is_frozen' => Yii::t('datacenter', '是否冻结'),
        ];
    }
    
    // 获取报表关系
    public function getReport()
    {
        return $this->hasOne(DcReport::className(), ['id'=>'report_id']);
    }
    
    // 获取数据集关系
    public function getSets()
    {
        return $this->hasOne(DcSets::className(), ['id'=>'set_id']);
    }
    
    // 获取用户数据集关系
    public function getUserSets()
    {
        return $this->hasOne(DcUserSets::className(), ['id'=>'user_set_id']);
    }
    
    // 获取数据集字段关系
    public function getSetsCol()
    {
        return $this->hasOne(DcSetsColumns::className(), ['id'=>'col_id']);
    }
    
    // 获取是否冻结
    public function getV_is_frozen($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_frozen));
    }
    
    // 返回字段别名
    public function getV_alias()
    {
        return "r_{$this->id}";
    }
    
    // 返回计算公式替换内容
    public function getV_label()
    {
        return ($this->label ? $this->label : $this->setsCol['v_label']);
    }
    
    // 是否允许排序
    public function getV_order()
    {
        return ($this->col_id>0&&$this->setsCol ? $this->setsCol['v_order'] : false);
    }
    
    // 返回计算公式替换内容
    public function getV_format_label()
    {
        return "{{$this->v_label}}";
    }
    
    // 返回字段默认值
    public function getV_default_value()
    {
        if($this->formula){
            return '0';
        }elseif($this->setsCol){
            return $this->setsCol['v_default_value'];
        }else{
            return '';
        }
    }
    
    // 保存后动作
    public function afterSave($insert, $changedAttributes)
    {
        // 更新数据报表的set_ids
        if($this->set_id && $this->report){
            self::$updateReportRelationSetIds[$this->report_id] = $this->report_id;
            Yii::$app->controller->off('afterAction', ['\datacenter\models\DcReportColumns', 'saveReportRelationSet']);
            Yii::$app->controller->on('afterAction', ['\datacenter\models\DcReportColumns', 'saveReportRelationSet']);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    // 更新数据集关联模型
    public static $updateReportRelationSetIds = [];
    public static function saveReportRelationSet()
    {
        // 更新数据报表的set_ids信息
        if(self::$updateReportRelationSetIds){
            $reportList = DcReport::find()->where(['id'=>self::$updateReportRelationSetIds])->with(['columns'])->all();
            foreach($reportList as $report){
                $setIds = $report['columns'] ? \yii\helpers\ArrayHelper::map($report['columns'], 'set_id', 'set_id') : [];
                $report['set_ids'] = implode(',', $setIds);
                $report->save(false);
            }
        }
        
    }
}
