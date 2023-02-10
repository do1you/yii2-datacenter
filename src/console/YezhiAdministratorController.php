<?php

/**
 * 配合业支相关计划任务
 */

namespace datacenter\console;

use Yii;
use webadmin\modules\authority\models\AuthUser;
use webadmin\modules\authority\models\AuthUserRole;
use datacenter\models\DcUserAuthority;

class YezhiAdministratorController extends \webadmin\console\CController
{
    
    /**
     * 预设的业支管理员角色
     */
    private $_roleId = ['20'];
    
    /**
     * 同步业支的管理账户
     */
    public function actionRsyncAdmin()
    {
        $args = func_get_args();
        $source = \datacenter\models\DcSource::findOne(["dbname"=>'clouddb','is_dynamic'=>'1']);
        $db = $source ? $source->getDbConnection() : null;
        
        if($db){
            // 同步用户
            $sql = "select -b.id as id,
                        a.name as name,
                        concat('YLLT_',a.login_name) as login_name,
                        a.password as password,
                        md5(md5(concat(a.login_name,a.id,'YLLT'))) as access_token,
                        a.mobile as mobile,
                        group_concat(c.store_id) as store_id 
                            from auth_user as a 
                            left outer join plat_customer_user as b on a.id=b.user_id
                            left outer join plat_customer_user_store as c on b.id=c.customer_user_id
                                where a.state=0 and c.store_id>0 ".
                                ($args ? " and a.login_name in ('".(implode("','", $args))."')" : "").
                                " group by b.id";
            $users = $db->createCommand($sql)->queryAll();
            $storeData = $roleData = $userIds = $data = [];
            foreach($users as $item){
                $userIds[] = $item['id'];
                
                // 组装角色数据
                if($this->_roleId && is_array($this->_roleId)){
                    foreach($this->_roleId as $rid){
                        $roleItem = [
                            'user_id' => $item['id'],
                            'role_id' => $rid,
                        ];
                        if(empty($roleColTitle)) $roleColTitle = array_keys($roleItem);
                        $roleData[] = $roleItem;
                    }
                }
                
                // 组装场所权限数据
                if(($storeIds = explode(',', $item['store_id']))){
                    $storeItem = [
                        'user_id' => $item['id'],
                        'source_id' => "3",
                        'source_type' => '2',
                    ];
                    $storeData[] = $storeItem;
                    
                    foreach($storeIds as $sid){
                        $storeItem = [
                            'user_id' => $item['id'],
                            'source_id' => "3_{$sid}",
                            'source_type' => '3',
                        ];
                        if(empty($storeColTitle)) $storeColTitle = array_keys($storeItem);
                        $storeData[] = $storeItem;
                    }
                }
                
                // 组装用户数据
                unset($item['store_id']);
                if(empty($colTitle)) $colTitle = array_keys($item);
                $data[] = $item;
            }
            
            // 写入用户
            if($colTitle && $data){
                $command = Yii::$app->db->createCommand()->batchInsert(AuthUser::tableName(), $colTitle, $data);
                $sql = $command->getRawSql();
                $sql = str_ireplace("INSERT INTO", "REPLACE INTO ", $sql);
                $successUserNum = $command->setRawSql($sql)->execute();
                
                // 删除其他用户
                if(empty($args)){
                    AuthUser::deleteAll([
                        'and',
                        ['not in', 'id', $userIds],
                        ['<', 'id', '0']
                    ]);
                }
            }
            
            // 写入角色
            if($roleColTitle && $roleData){
                AuthUserRole::deleteAll(['in', 'user_id', $userIds]);
                $successRoleNum = Yii::$app->db->createCommand()->batchInsert(AuthUserRole::tableName(), $roleColTitle, $roleData)->execute();
                
                // 删除其他角色
                if(empty($args)){
                    AuthUserRole::deleteAll([
                        'and',
                        ['not in', 'user_id', $userIds],
                        ['<', 'user_id', '0']
                    ]);
                }
            }
            
            // 写入场所权限
            if($storeColTitle && $storeData){
                DcUserAuthority::deleteAll(['in', 'user_id', $userIds]);
                $successStoreNum = Yii::$app->db->createCommand()->batchInsert(DcUserAuthority::tableName(), $storeColTitle, $storeData)->execute();
                
                // 删除其他场所权限
                if(empty($args)){
                    DcUserAuthority::deleteAll([
                        'and',
                        ['not in', 'user_id', $userIds],
                        ['<', 'user_id', '0']
                    ]);
                }
            }
        }
        
        return 0;
    }
    
}
