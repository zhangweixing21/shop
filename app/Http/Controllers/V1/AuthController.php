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