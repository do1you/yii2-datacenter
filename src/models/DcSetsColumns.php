<?php
/**
 * 数据库表 "dc_sets_columns" 的模型对象.
 * @property int $id 流水号
 * @property int $set_id 数据集
 * @property int $model_id 归属模型
 * @property string $name 名称
 * @property string $label 标签
 * @property int $is_search 是否可查
 * @property string $type 类型
 * @property string $formula 计算公式
 * @property string $fun 处理函数
 */

namespace datacenter\models;

use Yii;

class DcSetsColumns extends \webadmin\ModelCAR
{
    /**
     * 查询参数缓存
     */
    private $v_search_params_cache;
    
    /**
     * 表单扩展
     */
    public $search_params_text,$search_params_dd,$search_value_text,$switch_type;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_sets_columns';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['label', 'set_id'], 'required'],
            [['column_id'], 'required', 'when'=>function($model){
                return ($model->model_id>0 || $model->for_set_id>0);
            }],
            [['set_id', 'is_search', 'is_summary', 'paixu', 'is_back_search'], 'integer'],
            [['name', 'label', 'type', 'model_id', 'for_set_id', 'column_id', 'formula', 'fun', 'is_frozen', 'search_params', 'switch_type', 'resp_fun', 'sql_formula'], 'safe'],
            [['name', 'label', 'type', 'fun', 'search_value'], 'string', 'max' => 50],
            [['formula'], 'string', 'max' => 255],
            [['resp_fun'], 'string', 'max' => 100],
            [['label'], 'unique', 'filter' => "set_id='{$this->set_id}'"],
            [['search_params_text', 'search_params_dd', 'search_value_text'], 'safe', 'on'=>['insertForm','batchInsertForm','updateForm']],
            [['model_id'], 'required', 'on'=>'batchInsertForm'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'set_id' => Yii::t('datacenter', '数据集'),
            'model_id' => Yii::t('datacenter', '从属模型'),
            'for_set_id' => Yii::t('datacenter', '从属数据集'),
            'switch_source' => Yii::t('datacenter', '从属模型/数据集'),
            'column_id' => Yii::t('datacenter', '字段'),
            'name' => Yii::t('datacenter', '名称'),
            'label' => Yii::t('datacenter', '标签'),
            'is_search' => Yii::t('datacenter', '是否可查'),
            'is_back_search' => Yii::t('datacenter', '是否反查'),
            'is_summary' => Yii::t('datacenter', '是否汇总'),
            'type' => Yii::t('datacenter', '查询类型'),
            'formula' => Yii::t('datacenter', '计算公式'),
            'sql_formula' => Yii::t('datacenter', 'SQL公式'),
            'fun' => Yii::t('datacenter', '处理函数'),
            'resp_fun' => Yii::t('datacenter', '输出函数'),
            'paixu' => Yii::t('datacenter', '排序'),
            'is_frozen' => Yii::t('datacenter', '是否冻结'),
            'search_params' => Yii::t('datacenter', '查询参数'),
            'search_value' => Yii::t('datacenter', '查询默认值'),
            'search_params_text' => Yii::t('datacenter', '查询参数'),
            'search_params_dd' => Yii::t('datacenter', '查询字典'),
            'search_value_text' => Yii::t('datacenter', '查询默认值'),
            'switch_type' => Yii::t('datacenter', '归属类型'),
        ];
    }
    
    // 获取数据集关系
    public function getSets(){
        return $this->hasOne(DcSets::className(), ['id'=>'set_id']);
    }
    
    // 获取数据模型关系
    public function getModel(){
        return $this->hasOne(DcModel::className(), ['id'=>'model_id']);
    }
    
    // 获取数据数据集关系
    public function getForSets(){
        return $this->hasOne(DcSets::className(), ['id'=>'for_set_id']);
    }
    
    // 获取数据模型字段关系
    public function getColumn(){
        return $this->hasOne(DcAttribute::className(), ['model_id'=>'model_id', 'id'=>'column_id']);
    }
    
    // 获取数据模型字段关系
    public function getSetColumn(){
        return $this->hasOne(DcSetsColumns::className(), ['set_id'=>'for_set_id','id'=>'column_id']);
    }
    
    // 获取数据报表字段属性关系
    public function getReportColumns(){
        return $this->hasMany(DcReportColumns::className(), ['col_id' => 'id']);
    }
    
    // 获取数据集类型
    public function getV_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('config_type', ($val !== null ? $val : $this->type));
    }
    
    // 获取归属类型
    public function getV_switch_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_switch_type', ($val !== null ? $val : $this->switch_type));
    }
    
    // 获取数据集类型
    public function getV_fun($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_db_fun', ($val !== null ? $val : $this->fun));
    }
    
    // 获取输出函数
    public function getV_resp_fun($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_resp_fun', ($val !== null ? $val : $this->resp_fun));
    }
    
    // 获取查询默认值
    public function getV_search_value($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_search_defval', ($val !== null ? $val : $this->search_value));
    }
    
    // 获取是否冻结
    public function getV_is_frozen($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_frozen));
    }
    
    // 获取是否可查
    public function getV_is_search($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_search));
    }
    
    // 获取是否反查
    public function getV_is_back_search($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_back_search));
    }
    
    // 获取是否汇总
    public function getV_is_summary($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_summary));
    }
    
    // 返回格式化名称
    public function getV_name(){
        return $this->sets['title'].'.'.$this->label.($this->name ? "[{$this->name}]" : "");
    }
    
    // 返回格式化名称，不含数据集
    public function getV_colname(){
        return $this->label.($this->name ? "[{$this->name}]" : "");
    }
    
    // 返回字段格式化名称
    public function getV_column()
    {
        $column = $this->v_field;
        if(strpos($column, '.')!==false || preg_match("/[\+\-\*\/]/",$column)){
            $column = $this->sets ? $this->sets->formatSql($column) : $column;
            return "({$column})";
        }elseif($this->switch_type==2){
            return "{$this->forSets['v_alias']}.{$this->setColumn['v_alias']}";
        }else{
            return "{$this->model['v_alias']}.{$column}";
        }
    }
    
    // 返回带函数的格式化名称
    public function getV_fncolumn()
    {
        if($this->fun){
            return "{$this->fun}({$this->v_column})";
        }
        return $this->v_column;
    }
    
    
    // 返回字段格式化别名
    public function getV_column_alias()
    {
        return "{$this->v_fncolumn} as {$this->v_alias}";
    }
    
    // 返回字段别名
    public function getV_alias()
    {
        return "s_{$this->id}";
    }
    
    // 返回字段原名称
    public function getV_field()
    {
        return ($this->sql_formula ? $this->sql_formula : $this->name);
    }
    
    // 返回计算公式替换内容
    public function getV_label()
    {
        return $this->label;
    }
    
    // 是否函数表达式
    public function getV_isfn()
    {
        if($this->fun || preg_match("/(sum|count|avg|min|max|group\_concat)\(.*\)/i",$this->v_fncolumn)){
            return true;
        }
        return false;
    }
    
    // 是否允许排序
    public function getV_order()
    {
        return ((($this->model_id>0&&$this->column&&!$this->formula&&!$this->fun) || $this->sets['set_type']!='model') ? true : false);
    }
    
    // 返回计算公式替换标签
    public function getV_format_label()
    {
        return "{{$this->v_label}}";
    }
    
    // 返回计算公式替换名称
    public function getV_format_name()
    {
        return "{{$this->name}}";
    }
    
    // 返回字段默认值
    public function getV_default_value()
    {
        if($this->formula){
            return '0';
        }elseif($this->column){
            return $this->column['v_default_value'];
        }else{
            return '';
        }
    }
    
    // 配置参数数组
    public function getV_search_params()
    {
        if($this->v_search_params_cache === null){
            $search_params = $this->search_params ? $this->search_params : "";
            $result = [];
            $list = explode("\n", str_replace("\r","",$search_params));
            if($list){
                foreach($list as $val){
                    if(stripos($val,'|')===false){
                        $result[$val] = $val;
                    }else{
                        list($k,$v) = explode('|',$val);
                        $result[$k] = $v;
                    }
                }
            }
            $this->v_search_params_cache = $result;
        }
        
        return $this->v_search_params_cache;
    }
    
    // 配置参数网络
    public function getV_search_ajax()
    {
        $search_params = $this->search_params ? $this->search_params : "";
        if(stripos($search_params, '.')!==false){
            if(substr($search_params,0,1)=='/'){
                return \yii\helpers\Url::to(['default/select2','s'=>$this->sets['mainModel']['source_db'],'key'=>trim($search_params,'/')]);
            }else{
                return \yii\helpers\Url::to(['/config/sys-config/select2','key'=>$search_params]);
            }
        }elseif($search_params){
            return \yii\helpers\Url::to($search_params);
        }
    }
    
    // 返回默认值
    public function getV_search_defval()
    {
        $finance_sec = 36000; // 定义营业日自然时间误差10个小时
        $defaultValue = $this->search_value;
        switch($defaultValue){
            case 'doyesterday': // 昨天营业日
                $currTime = $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d')) - 3600 * 24 + $finance_sec));
                $endTime = date('Y-m-d H:i:s', (strtotime($startTime) + 3600 * 24 - 1));
                break;
            case 'yesterday': // 昨天自然日
                $currTime = $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d')) - 3600 * 24));
                $endTime = date('Y-m-d H:i:s', (strtotime($startTime) + 3600 * 24 - 1));
                break;
            case 'dotoday': // 当天营业日
                $currTime = $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d')) + $finance_sec));
                $endTime = date('Y-m-d H:i:s', (strtotime($startTime) + 3600 * 24 - 1));
                break;
            case 'today': // 当天自然日
                $currTime = $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d'))));
                $endTime = date('Y-m-d H:i:s', (strtotime($startTime) + 3600 * 24 - 1));
                break;
            case 'prevweek': // 上周
                $currTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime('-1 week', time())))));
                $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime('-1 week last monday', time())))));
                $endTime = date('Y-m-d H:i:s', (strtotime($startTime) + 3600 * 24 * 7 - 1));
                break;
            case 'currweek': // 本周
                $currTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime('-0 week', time())))));
                $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime('-0 week last monday', time())))));
                $endTime = date('Y-m-d H:i:s', (strtotime($startTime) + 3600 * 24 * 7 - 1));
                break;
            case 'prevmonth': // 上月
                $currTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime('-1 month')))));
                $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-01', strtotime('-1 month')))));
                $endTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-t', strtotime('-1 month')))));
                break;
            case 'currmonth': // 本月
                $currTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-d', strtotime('-0 month')))));
                $startTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-01', strtotime('-0 month')))));
                $endTime = date('Y-m-d H:i:s', (strtotime(date('Y-m-t', strtotime('-0 month')))));
                break;
            case '1days': // 最近XX天
            case '3days':
            case '7days':
            case '10days':
            case '15days':
            case '30days':
            case '90days':
                $currTime = $startTime = date('Y-m-d H:i:s', (strtotime("-{$defaultValue}")));
                $endTime = date('Y-m-d H:i:s');
                break;
        }
        
        if(!empty($startTime) && !empty($endTime)){
            if($this['type']=='datetimerange'){
                $defaultValue = date('Y-m-d H:i:s',strtotime($startTime)).' 至 '.date('Y-m-d H:i:s',strtotime($endTime));
            }elseif($this['type']=='daterange'){
                $defaultValue = date('Y-m-d',strtotime($startTime)).' 至 '.date('Y-m-d',strtotime($endTime));
            }elseif($this['type']=='datetime'){
                $defaultValue = date('Y-m-d H:i:s',strtotime($currTime));
            }elseif($this['type']=='date'){
                $defaultValue = date('Y-m-d',strtotime($currTime));
            }elseif($this['type']=='time'){
                $defaultValue = date('H:i:s',strtotime($currTime));
            }elseif($this['type']=='dateyear'){
                $defaultValue = date('Y',strtotime($currTime));
            }
        }
        
        return $defaultValue;
    }
    
    // 删除判断
    public function delete()
    {
        if($this->id<0){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '系统内置数据集，禁止删除！'));
        }
        
        if($this->reportColumns){
            foreach($this->reportColumns as $item){
                $item->delete();
            }
        }
        
        return parent::delete();
    }
    
    // 查询后
    public function afterFind()
    {
        if(in_array($this->type, ['mask', 'selectajax', 'selectajaxmult'] )){
            $this->search_params_text = $this->search_params;
        }elseif(in_array($this->type, ['dd', 'ddmulti', 'ddselect2', 'ddselect2multi'] )){
            $this->search_params_dd = $this->search_params;
        }
        if(!in_array($this->type, ['datetimerange', 'daterange', 'datetime', 'date', 'dateyear'] )){
            $this->search_value_text =  $this->search_value;
        }
        
        $this->switch_type = $this->for_set_id>0 ? 2 : 1;
        return parent::afterFind();
    }
    
    // 保存前动作
    public static $_updateModelIds = [];
    public static $_updateSetsIds = [];
    public function beforeSave($insert)
    {
        // 判断归属类型
        if($this->switch_type==2){
            $this->model_id = 0;
            if($this->for_set_id>0 && $this->column_id>0 && $this->setColumn){
                $this->name = $this->setColumn['name'];
            }
        }elseif($this->switch_type==1){
            $this->for_set_id = 0;
            if($this->model_id>0 && $this->column_id>0 && $this->column){
                $this->name = $this->column['name'];
            }
        }
        
        // 验证模型/数据集是否可保存
        if($this->sets && $this->sets['set_type']=='model' && $this->sets->mainModel){
            if($this->for_set_id && !in_array($this->for_set_id, DcSetsColumns::$_updateSetsIds)){
                $setLists = $this->sets->getV_relation_sets();
                $query = new \yii\db\Query();
                $query->from("{$this->sets['mainModel']['v_model']} as {$this->sets['mainModel']['v_alias']}");
                $this->sets->joinQuerySets($query, $setLists);
                if($setLists){
                    $model = reset($setLists);
                    throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的数据集关系')."{$model['v_title']}");
                }else{
                    self::$_updateSetsIds[] = $this->for_set_id; // 记录已存在模型关系，不用多次更新
                }
            }elseif($this->model_id && !in_array($this->model_id, DcSetsColumns::$_updateModelIds)){
                $models = $this->sets->getV_relation_models();
                $models[$this->model_id] = $this->model;
                unset($models[$this->sets['mainModel']['id']]);
                $query = new \yii\db\Query();
                $query->from("{$this->sets['mainModel']['v_model']} as {$this->sets['mainModel']['v_alias']}");
                $this->sets->mainModel->joinModel($query, $models);
                if($models){
                    $model = reset($models);
                    throw new \yii\web\HttpException(200, Yii::t('datacenter','未关联的模型关系')."{$model['tb_label']}({$model['id']}.{$model['tb_name']})");
                }else{
                    self::$_updateModelIds[] = $this->model_id; // 记录已存在模型关系，不用多次更新
                }
            }
        }
        
        // 查询默认值和参数保存
        if(in_array($this->scenario, ['insertForm', 'updateForm'])){
            if(in_array($this->type, ['mask', 'selectajax', 'selectajaxmult'] )){
                $this->search_params = $this->search_params_text;
            }elseif(in_array($this->type, ['dd', 'ddmulti', 'ddselect2', 'ddselect2multi'] )){
                $this->search_params = $this->search_params_dd;
            }
            if(!in_array($this->type, ['datetimerange', 'daterange', 'datetime', 'date', 'dateyear'] )){
                $this->search_value =  $this->search_value_text;
            }
        }
        
        return parent::beforeSave($insert);
    }
    
    // 保存后动作
    public function afterSave($insert, $changedAttributes)
    {
        // 更新数据集的relation_models
        if($this->model_id && $this->sets && $this->sets['set_type']=='model'){
            self::$updateSetRelationModelIds[$this->set_id] = $this->set_id;
            Yii::$app->controller->off('afterAction', ['\datacenter\models\DcSetsColumns', 'saveSetRelationModel']);
            Yii::$app->controller->on('afterAction', ['\datacenter\models\DcSetsColumns', 'saveSetRelationModel']);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    // 更新数据集关联模型
    public static $updateSetRelationModelIds = [];
    public static function saveSetRelationModel()
    {
        // 更新数据集的relation_models信息
        if(self::$updateSetRelationModelIds){
            $setList = DcSets::find()->where(['id'=>self::$updateSetRelationModelIds])->with(['columns'])->all();
            foreach($setList as $set){
                $modelIds = $set['columns'] ? \yii\helpers\ArrayHelper::map($set['columns'], 'model_id', 'model_id') : [];
                $set['relation_models'] = implode(',', $modelIds);
                $set->save(false);
            }
        }
        
    }
}
