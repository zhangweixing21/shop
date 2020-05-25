<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2019/12/24
 * Time: 18:13
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;


class AuthController extends BaseController
{
    //设置使用guard为api选项验证，请查看config/auth.php的guards设置项，重要！
    protected $guard = 'api';

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('refresh', ['except' => ['login','register','test']]);
    }

    public function test(){
        return $this->errorHandler(1000);
    }

    /**
     * 注册
     * @param Request $request
     * @return array
     */
    public function register(Request $request)
    {


    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $rules = [
            'tel' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/'],
            'password' => ['required', 'min:6', 'max:16'],
            'push_id' => ['required']
        ];
        $message = [
            'tel.required' => '手机号必须填写',
            'tel.regex' => '手机号格式不正确',
            'password.required' => '密码必须填写',
            'password.min' => '密码长度不能小于6个字符',
            'password.max' => '密码长度不能大于16个字符',
            'push_id.required' => '参数错误',
        ];
        $credentials = $request->all();
        $validator = Validator::make($credentials, $rules,$message);

        // 验证格式
        if ($validator->fails()) {
            return $this->response->array(['status' => 1000, 'error' => $validator->errors()->first()]);
        }
        //判断push_id是否相同实现互斥登录
        $user = DB::connection("mysql_center")->table('user')->select('id','push_id','password')->where(['tel' => $credentials['tel']])->first();
        if (!$user){
            return $this->errorHandler(1002);
        }

        if ($user->push_id != $credentials['push_id']){
            if ($user->push_id){
//                $result = Jpush::ziMessage($credentials['push_id'],$user->push_id,'out','您的手机在另一台手机上登陆了,请重新登陆!');

            }

        }

        //修改登录时间和推送id
        DB::connection("mysql_center")->table('user')->where('id', $user->id)->update(['push_id' => $credentials['push_id']]);
        //绑定别名(使用用户ID来区分不同的别名)
//        $result = JPushService::updateAlias($credentials['push_id'], 'user_id_' . $user->id);

        $params = $request->only('tel', 'password');
        //获取token
        if ($token = $this->guard()->attempt($params)) {
            return $this->respondWithToken($token);
        }

        return $this->errorHandler(1001);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {

    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 0,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * 忘记密码
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function forgetpwd(Request $request){

    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard($this->guard);
    }





}