<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2019/12/25
 * Time: 13:51
 */

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Validator;
use Qcloud\Sms\SmsSingleSender;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests,Helpers;

    public function __construct(){

    }

    /**
     * 错误消息
     * @param $status
     * @return array
     */
    public function errorHandler($status)
    {
        $errors = [
            1000 => '参数错误！',
            1001 => '账号密码错误！',
            1002 => '用户不存在！',
            1003 => '商品未上架！',
            1004 => '商品已结收藏过了！',
            1005 => '收藏成功！',
            1006 => '收藏失败！',
            1007 => '删除成功！',
            1008 => '删除失败！',
        ];

        $ret = [];
        $ret['status'] = $status;
        $ret['msg'] = $errors[$status] ? : "error";

        return response()->json($ret);
    }

    /**
     * 数据返回
     * @param $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function formatAsjson($data,$status = 0)
    {
        return $this->response->array(['status' => $status,'info' =>$data]);
    }

    /**
     *连接redis
     */
    public static function getRedis(){
        $redis = new \redis();
        $redis->connect('127.0.0.1',6379,5); //本机6379端口，5秒超时
        $redis->select(1);      //1库
        return $redis;
    }

    /**
     * 短信验证码
     * @param tel str 手机号
     *
     * @param Request $request
     * @return array
     */
    public function textMessage(Request $request){
        $payload = $request->only( 'tel');
        $rules = [
            'tel' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/']
        ];
        $message = [
            'tel.required' => '手机号必须填写',
            'tel.regex' => '手机号格式不正确'
        ];
        $validator = Validator::make($payload, $rules,$message);

        // 验证格式
        if ($validator->fails()) {
            return $this->response->array(['status' => 1000, 'msg' => $validator->errors()]);
        }

        //获取短信验证码
        $result = $this->easysms($payload['tel']);
        if ($result){
            return $this->errorHandler(1998);
        }else{
            return $this->errorHandler(1999);
        }

    }

    /**
     * 获取短信验证码
     * easysms - sdk发送
     * @param $phoneNumbers static 电话号码
     * @return bool true为发送成功
     */
    public function easysms($phoneNumbers)
    {
        $templateId = 508113;
        $code = str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_BOTH);
        try {
            $ssender = new SmsSingleSender(config('sms.app_id'), config('sms.app_key'));
            $params = [$code]; // 验证码
            // 86 - 国家电话代码 ， 电话号码，短信正文模板id，验证码，短信签名，默认为空，默认为空
            // 签名参数未提供或者为空时，会使用默认签名发送短信
            $result = $ssender->sendWithParam("86", $phoneNumbers, $templateId,$params, "", "", "");
//            Log::info('短信返回数据: '.$result);
            $result = json_decode($result,true); // 这里会返回一个发送短信的结果
            if( $result['errmsg'] == 'OK' ) {
                $redis = self::getRedis();
                try {
                    $redis->ping();
                } catch (Exception $e) {
                    $redis = self::getRedis();
                }
                $redis->set($phoneNumbers,$code,30000);
                return true;
            }
        } catch(\Exception $e) {
            $error = [
                'msg' => $e,
                'phone' => $phoneNumbers,
                'time' => date('Y-m-d H:i:s',time())
            ];
            Log::info('短信捕捉错误数据: '.json_encode($error));
        }

        return false;

    }


}