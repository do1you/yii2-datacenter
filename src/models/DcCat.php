<?php
/**
 * 数据库表 "dc_cat" 的模型对象.
 * @property int $id 流水号
 * @property string $name 分类名称
 * @property int $parent_id 上级分类
 * @property int $paixu 排序
 */

namespace datacenter\models;

use Yii;

class DcCat extends \webadmin\ModelCAR
{
    
    use \webadmin\TreeTrait;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'dc_cat';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id', 'paixu'], 'integer'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('datacenter', '流水号'),
            'name' => Yii::t('datacenter', '分类名称'),
            'parent_id' => Yii::t('datacenter', '上级分类'),
            'paixu' => Yii::t('datacenter', '排序'),
        ];
    }
    
    // 返回节点都是目录分类
    public function getType()
    {
        return 'folder';
    }
}
