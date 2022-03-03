<?php
/**
 * 模型对象PayChannel的增删改查控制器方法.
 */
namespace datacenter\controllers;

use Yii;

class DefaultController extends \webadmin\BController
{
    // 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('datacenter', '数据中心');
        Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
        
        return parent::beforeAction($action);
    }
    
    // 测试
    public function actionIndex()
    {
        /*$str = '{"sign":"W30inuPyeTU0fverp9kJC6F95ajLJcw1tXAlZ0WwM1qNnwtJ8YIhRSzphEgm0Qpk/pTMt3hZfHV/A/DiLJ2f/TW4eSIAVyOl95KlUwGzJnQjvlEYYu57gZUW6UsaEOyK/RBM83SwjEqfWaay7KRz1/gJVUrc5lmLu8QDHrMfW5w=","ysepay_online_trade_refund_query_response":{"code":"10000","msg":"Success","trade_no":"01O211221447255560","out_trade_no":"20211221100621275561","out_request_no":"202112211033070000124495","refund_state":"success","account_date":"2021-12-21","refund_reason":"正常退款","total_amount":1.0,"refund_amount":1.0,"funds_state":"success","funds_dynamics":"[{\"channelRecvSn\":\"50101000562021122115579803920\",\"channelSendSn\":\"1012112214482468661\",\"marketingRefundDetail\":\"{\\\"fee_type\\\":\\\"CNY\\\",\\\"refund_fee\\\":1.0,\\\"cash_refund_fee\\\":1.0,\\\"settlement_refund_fee\\\":1.0,\\\"coupon_refund_fee\\\":0.0}\",\"refundamount\":1,\"refundsn\":\"01R211221448246866\",\"sendChannelTime\":1640054010000,\"state\":\"00\"}]","src_fee_flag":"01","has_refund_src_fee":0.0,"payee_fee_flag":"01","has_refund_payee_fee":0.0,"payer_fee_flag":"01","has_refund_payer_fee":0.0,"markting_refund_detail":"{\"fee_type\":\"CNY\",\"refund_fee\":1.0,\"cash_refund_fee\":1.0,\"settlement_refund_fee\":1.0,\"coupon_refund_fee\":0.0}"}}';
        $str1 = '{"sign":"W30inuPyeTU0fverp9kJC6F95ajLJcw1tXAlZ0WwM1qNnwtJ8YIhRSzphEgm0Qpk/pTMt3hZfHV/A/DiLJ2f/TW4eSIAVyOl95KlUwGzJnQjvlEYYu57gZUW6UsaEOyK/RBM83SwjEqfWaay7KRz1/gJVUrc5lmLu8QDHrMfW5w=","ysepay_online_trade_refund_query_response":{"code":"10000","msg":"Success","trade_no":"01O211221447255560","out_trade_no":"20211221100621275561","out_request_no":"202112211033070000124495","refund_state":"success","account_date":"2021-12-21","refund_reason":"正常退款","total_amount":1.0,"refund_amount":1.0,"funds_state":"success","funds_dynamics":"[{\"channelRecvSn\":\"50101000562021122115579803920\",\"channelSendSn\":\"1012112214482468661\",\"marketingRefundDetail\":\"{\\"fee_type\\":\\"CNY\\",\\"refund_fee\\":1.0,\\"cash_refund_fee\\":1.0,\\"settlement_refund_fee\\":1.0,\\"coupon_refund_fee\\":0.0}\",\"refundamount\":1,\"refundsn\":\"01R211221448246866\",\"sendChannelTime\":1640054010000,\"state\":\"00\"}]","src_fee_flag":"01","has_refund_src_fee":0.0,"payee_fee_flag":"01","has_refund_payee_fee":0.0,"payer_fee_flag":"01","has_refund_payer_fee":0.0,"markting_refund_detail":"{\"fee_type\":\"CNY\",\"refund_fee\":1.0,\"cash_refund_fee\":1.0,\"settlement_refund_fee\":1.0,\"coupon_refund_fee\":0.0}"}}';
        preg_match_all('/"([a-zA-Z0-9\_]+)\\\*"\s*:\s*(\d+\.0+),?/',$str,$params);
        //$params = !empty($params[1])&&!empty($params[2]) ? array_combine($params[1], $params[2]) : [];
        print_r($params); // 存在精度的数组
        exit;*/
        return $this->render('index', [
        ]);
    }
}
    