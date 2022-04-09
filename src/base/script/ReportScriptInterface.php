<?php

namespace datacenter\base\script;

/**
 * 脚本数据提供接口
 * @author 统一
 *
 */
interface ReportScriptInterface
{
    /**
     * 返回数据源
     */
    public function getModels();
    
}