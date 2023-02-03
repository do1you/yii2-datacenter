<?php
/**
 * 数据库表 "dc_source" 的模型对象.
 * @property int $id 流水号
 * @property string $name 源名称
 * @property string $dbtype 数据库类型
 * @property string $dbhost 数据库IP
 * @property string $dbport 数据库端口
 * @property string $dbname 数据库名称
 * @property string $dbuser 数据库用户名
 * @property string $dbpass 数据库密码
 * @property int $is_dynamic 是否动态库
 * @property string $dchost 动态库IP
 * @property string $dcport 动态库端口
 * @property string $dcname 动态库名称
 * @property string $dcuser 动态库用户名
 * @property string $dcpass 动态库密码
 * @property string $dctable 动态库取数表
 */

namespace datacenter\models;

use Yii;
use function Opis\Closure\serialize;

class DcSource extends \webadmin\ModelCAR
{
    /**
     * 记录数据库实例缓存
     */
    public static $cacheDbs = [];
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_source';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name', 'dbtype', 'dbhost', 'dbport', 'dbname', 'dbuser', 'dbpass',], 'required'],
            [['dchost', 'dcport', 'dcname', 'dcuser', 'dcpass', 'dctable', 'dcselect'], 'required', 'when' => function ($model) {
                return $model->is_dynamic;
            },],
            [['is_dynamic'], 'integer'],
            [['name', 'dbtype', 'dbhost', 'dbport', 'dbname', 'dbuser', 'dbpass', 'dchost', 'dcport', 'dcname', 'dcuser', 'dcpass', 'dctable', 
                'dcselect', 'dcsession', 'dcident'], 'string', 'max' => 50],
            [['dcwhere'], 'string', 'max' => 150],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'name' => Yii::t('datacenter', '源名称'),
            'dbtype' => Yii::t('datacenter', '数据库类型'),
            'dbhost' => Yii::t('datacenter', '数据库IP'),
            'dbport' => Yii::t('datacenter', '数据库端口'),
            'dbname' => Yii::t('datacenter', '数据库名称'),
            'dbuser' => Yii::t('datacenter', '数据库用户名'),
            'dbpass' => Yii::t('datacenter', '数据库密码'),
            'is_dynamic' => Yii::t('datacenter', '是否动态库'),
            'dchost' => Yii::t('datacenter', '动态库IP'),
            'dcport' => Yii::t('datacenter', '动态库端口'),
            'dcname' => Yii::t('datacenter', '动态库名称'),
            'dcuser' => Yii::t('datacenter', '动态库用户名'),
            'dcpass' => Yii::t('datacenter', '动态库密码'),
            'dctable' => Yii::t('datacenter', '动态库取数表'),
            'dcwhere' => Yii::t('datacenter', '动态库条件'),
            'dcident' => Yii::t('datacenter', '动态选择主键'),
            'dcselect' => Yii::t('datacenter', '动态选择名称'),
            'dcsession' => Yii::t('datacenter', '动态SESSION'),
        ];
    }
    
    // 获取源模型关系
    public function getModels()
    {
        return $this->hasMany(DcModel::className(), ['source_db' => 'id']);
    }
    
    // 获取是否动态库
    public function getV_is_dynamic($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum', ($val !== null ? $val : $this->is_dynamic));
    }
    
    // 获取数据库类型
    public function getV_dbtype($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('dc_dbtype_list', ($val !== null ? $val : $this->dbtype));
    }
    
    // 返回数据库连接实例
    public function getSourceDb($f5 = false, $isParent = false, $link = false)
    {
        try{
            $model = $this;
            if($isParent===false && $model['is_dynamic']=='1'){ // 动态库
                $dynamicInfo = $this->getDynamicInfo($f5);
                
                $db = $dynamicInfo? $model->getDbConnection($dynamicInfo, $f5) : null;
            }else{
                $db = $model->getDbConnection(null, $f5);
            }
            
            $link && $db && $db->open();
        }catch(\yii\base\Exception $e) {
            return false;
        }

        return ($db ? $db : false);
    }
    
    // 获取动态库数据库列表
    public function getDynamicList($f5=false)
    {
        $model = $this;
        $cacheKey = 'datacenter/dynamicdb/'.($model['id'] ? $model['id'] : md5(serialize($model->attributes)));
        
        if(($list = Yii::$app->cache->get($cacheKey))===false || $f5){
            $sql = "select * from {$model['dctable']}".($model['dcwhere'] ? " where {$model['dcwhere']} " : "")." order by ".($model['dcselect'] ? $model['dcselect'] : ($model['dcname'] ? $model['dcname'] : '1'));
            $list = [];
            if(($db = $this->getSourceDb(false, true))){
                try{
                    $rows = $db->createCommand($sql)->queryAll();
                    foreach($rows as $row){
                        $list[$row[$model['dcident']]] = [
                            'parent_id' => $this->id,
                            'id' => $row[$model['dcident']],
                            'name' => $row[$model['dcselect']],
                            'dbtype' => $this->dbtype,
                            'dbhost' => $row[$model['dchost']],
                            'dbport' => (isset($row[$model['dcport']]) ? $row[$model['dcport']] : ($model['dcport'] ? $model['dcport'] : '3306')),
                            'dbname' => (isset($row[$model['dcname']]) ? $row[$model['dcname']] : ($model['dcname'] ? $model['dcname'] : 'mysql')),
                            'dbuser' => (isset($row[$model['dcuser']]) ? $row[$model['dcuser']] : ($model['dcuser'] ? $model['dcuser'] : 'root')),
                            'dbpass' => (isset($row[$model['dcpass']]) ? $row[$model['dcpass']] : ($model['dcpass'] ? $model['dcpass'] : 'root')),
                        ];
                    }
                    $model['id'] && Yii::$app->cache->set($cacheKey, $list, 7200);
                }catch(\yii\base\Exception $e) {
                }
            }
        }
        
        return $list;
    }
    
    // 获取含权限的动态库数据库列表
    public function getAuthorityDynamicList($userId,$f5=false)
    {
        $list = $this->getDynamicList($f5);
        if($userId!='1'){
            if($userId){
                $ids = DcRoleAuthority::model()->getCache('getAuthorityIds', [$userId,'3']);
                $userIds = DcUserAuthority::model()->getCache('getAuthorityIds', [$userId,'3']);
                $ids = \yii\helpers\ArrayHelper::merge($ids, $userIds);
                if(!empty($ids[$this->id]) && is_array($ids[$this->id])){
                    foreach($list as $key=>$item){
                        if(!in_array($item['id'],$ids[$this->id])){
                            unset($list[$key]);
                        }
                    }
                }else{
                    $list = [];
                }
            }elseif(($currIdent = Yii::$app->session[$this['v_sessionName']]) && isset($list[$currIdent])){
                $list = [$currIdent => $list[$currIdent]];
            }
        }
        
        return $list;
    }
    
    // 获取动态度选中的数据库信息
    public function getDynamicInfo($f5=false)
    {
        $dynamicInfos = $this->getAuthorityDynamicList(Yii::$app->user->id,$f5);
        $currIdent = Yii::$app->session[$this['v_sessionName']];
        
        // 未匹配到权限数据，取第一个
        if(!$currIdent || !isset($dynamicInfos[$currIdent])){
            $keys = array_keys($dynamicInfos);
            $currIdent = reset($keys);
            Yii::$app->session[$this['v_sessionName']] = $currIdent;
        }
        
        return (isset($dynamicInfos[$currIdent]) ? $dynamicInfos[$currIdent] : null);
    }
    
    // 返回数据库名
    public function getV_dbname()
    {
        if($this->is_dynamic=='1'){
            $dynamicInfo = $this->getDynamicInfo();
            return $dynamicInfo['dbname'];
        }else{
            return $this->dbname;
        }
    }
    
    // 返回动态库键名选择
    public function getV_sessionName()
    {
        return ($this['dcsession'] ? $this['dcsession'] : '_curr_set_'.$this['id']);
    }
    
    // 返回数据库连接
    public function getDbConnection($model=null, $f5=false)
    {
        $model = $model===null ? $this : $model;
        if(is_array($model) && isset($model['parent_id'])){
            $key = $model['id'] ? "dynamic_{$model['parent_id']}_{$model['id']}" : md5(serialize($model));
        }else{
            $key = $model['id'] ? $model['id'] : md5(serialize($model->attributes));
        }
        if(!isset(self::$cacheDbs[$key]) || $f5){
            self::$cacheDbs[$key] = Yii::createObject([
                'class' => 'yii\db\Connection',
                'dsn' => "{$model['dbtype']}:host={$model['dbhost']};port={$model['dbport']};dbname={$model['dbname']}",
                'enableSchemaCache' => true,
                'schemaCacheDuration' => 86400,
                'emulatePrepare' => true,
                'username' => $model['dbuser'],
                'password' => $model['dbpass'],
                'charset' => 'utf8',
                'attributes' => [\PDO::ATTR_TIMEOUT => 8,],
            ]);
        }
        
        return self::$cacheDbs[$key];
    }
    
    // 返回数据表的Schema
    public function getTableSchemas($schema = '')
    {
        if(($db = $this->getSourceDb())){
            return $db->schema->getTableSchemas($schema);
        }
        
        return [];
    }
    
    // 返回数据表名称
    public function getTableNames($schema = '')
    {
        if(($db = $this->getSourceDb())){
            return $db->schema->getTableNames($schema);
        }
        
        return [];
    }
    
    // 返回数据表备注，目前仅支持MYSQL
    public function getTableComments($schema = '')
    {
        if(($db = $this->getSourceDb())){
            
            switch($this->dbtype){
                case 'mysql':
                case 'mysqli':
                    $sql = 'SHOW TABLE STATUS';
                    if ($schema !== '') {
                        $sql .= ' FROM ' . $db->schema->quoteSimpleTableName($schema);
                    }
                    
                    return \yii\helpers\ArrayHelper::map($db->createCommand($sql)->queryAll(), 'Name', 'Comment');
                    break;
                default:
                    throw new \yii\web\HttpException(500, Yii::t('datacenter', '暂未实现数据库类型！').$this->dbtype);
                    break;
            }
        }
        
        return [];
    }
    
    // 删除判断
    public function delete()
    {
        if($this->models){
            throw new \yii\web\HttpException(200, Yii::t('datacenter', '该数据源下存在模型，请先删除模型！'));
        }
        
        return parent::delete();
    }
    
    // 预处理数据
    public function findModel($condition, $muli = false)
    {
        $query = parent::findByCondition($condition);
        
        return ($muli ? $query->all() : $query->one());
    }
    
    // 将表和字段进行保存为数据模型
    public function initDataSet()
    {
        $comments = $this->getTableComments();
        $schemas = $this->getTableSchemas();
        $list = $this->getModels()->with(['columns'])->all();
        $models = [];
        foreach($list as $item){
            if(!isset($models[$item['tb_name']])) $models[$item['tb_name']] = [];
            $models[$item['tb_name']][] = $item;
        }
        $tbNum = $colNum = 0;
        
        foreach($schemas as $schema){
            $tb = $schema->name;
            $modelList = isset($models[$tb])&&is_array($models[$tb]) ? $models[$tb] : [DcModel::model()];
            foreach($modelList as $model){
                if($model->load([
                    'source_db' => $this->id,
                    'tb_name' => $tb,
                    'tb_label' => ($model['tb_label'] ? $model['tb_label'] : (isset($comments[$tb]) ? $comments[$tb] : '')),
                    'update_time' => ($model['tb_label']&&$model['update_time'] ? $model['update_time'] : date('Y-m-d H:i:s')),
                ],'') && $model->save()){
                    $columns = isset($models[$tb]) ? $model['columns'] : [];
                    $columns = \yii\helpers\ArrayHelper::map($columns, 'name', 'v_self');
                    
                    foreach($schema->columns as $column){
                        $col = $column->name;
                        $cModel = isset($columns[$col]) ? $columns[$col] : DcAttribute::model();
                        if($cModel->load([
                            'model_id' => $model['id'],
                            'name' => $col,
                            'label' => ($cModel['label'] ? $cModel['label'] : $column->comment),
                            'type' => ($cModel['type'] ? $cModel['type'] : $column->type), // phpType
                            'length' => ($cModel['length'] ? $cModel['length'] : $column->size),
                            'default' => (strlen($cModel['default'])>0 ? $cModel['default'] : $column->defaultValue),
                        ],'') && $cModel->save(false)){
                            unset($columns[$col]);
                            $colNum++;
                        }
                    }
                    
                    foreach($columns as $item){
                        $item->delete();
                    }
                    
                    unset($models[$tb]);
                    $tbNum++;
                }
            }
        }

        return $tbNum;
    }
}
