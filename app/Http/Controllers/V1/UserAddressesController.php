<?php

namespace App\Http\Controllers\V1;

use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Http\Request;
use App\Models\UserAddress;
use App\Http\Requests\UserAddressRequest;

class UserAddressesController extends AuthController
{
    /**
     * 新增收货地址
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function addresses(Request $request){
        $params = $request->only(['province','city','district','address','contact_name','contact_phone','is_default']);
        $rules = [
            'province' => ['required'],
            'city' => ['required'],
            'district' => ['required'],
            'address' => ['required'],
            'contact_name' => ['required'],
            'contact_phone' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|191|(147))\\d{8}$/']
        ];
        $message = [
            'province.required' => '省必须填写',
            'city.required' => '市必须填写',
            'district.required' => '区必须填写',
            'address.required' => '详细地址必须填写',
            'contact_name.required' => '收货人必须填写',
            'contact_phone.required' => '手机号必须填写',
            'contact_phone.regex' => '手机号格式不正确',
        ];

        $validator = Validator::make($params, $rules,$message);
        // 验证格式
        if ($validator->fails()) {
            return $this->formatAsjson($validator->errors()->first(),1000);
        }
        $users = $this->guard()->user();
        $Address = new UserAddress();
        $Address->user_id = $users->id;
        $Address->province = $params['province'];
        $Address->city = $params['city'];
        $Address->district = $params['district'];
        $Address->address = $params['address'];
        $Address->contact_name = $params['contact_name'];
        $Address->contact_phone = $params['contact_phone'];
        $Address->is_default = $params['is_default'];
        $Address->created_at = date('Y-m-d H:i:s');
        $Address->updated_at = date('Y-m-d H:i:s');
        if ($Address->save()){
            if ($params['is_default'] == 1){
                DB::table('user_addresses')->where([['user_id','=',$users->id],['id','!=',$Address->id],['is_default','=',1]])->update(['is_default' => 0]);
            }
            return $this->errorHandler(1009);
        }else{
            return $this->errorHandler(1010);
        }

    }

    public function upresses(Request $request){
        $params = $request->only(['province','city','district','address','contact_name','contact_phone','is_default','id']);
        $rules = [
            'id' => ['required'],
            'province' => ['required'],
            'city' => ['required'],
            'district' => ['required'],
            'address' => ['required'],
            'contact_name' => ['required'],
            'contact_phone' => ['required','regex:/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|191|(147))\\d{8}$/']
        ];
        $message = [
            'id.required' => '参数错误',
            'province.required' => '省必须填写',
            'city.required' => '市必须填写',
            'district.required' => '区必须填写',
            'address.required' => '详细地址必须填写',
            'contact_name.required' => '收货人必须填写',
            'contact_phone.required' => '手机号必须填写',
            'contact_phone.regex' => '手机号格式不正确',
        ];

        $validator = Validator::make($params, $rules,$message);
        // 验证格式
        if ($validator->fails()) {
            return $this->formatAsjson($validator->errors()->first(),1000);
        }
        $users = $this->guard()->user();
        $Address = UserAddress::find($params['id']);
        $Address->user_id = $users->id;
        $Address->province = $params['province'];
        $Address->city = $params['city'];
        $Address->district = $params['district'];
        $Address->address = $params['address'];
        $Address->contact_name = $params['contact_name'];
        $Address->contact_phone = $params['contact_phone'];
        $Address->is_default = $params['is_default'];
        $Address->updated_at = date('Y-m-d H:i:s');
        if ($Address->save()){
            if ($params['is_default'] == 1){
                DB::table('user_addresses')->where([['user_id','=',$users->id],['id','!=',$Address->id],['is_default','=',1]])->update(['is_default' => 0]);
            }
            return $this->errorHandler(1009);
        }else{
            return $this->errorHandler(1010);
        }

    }

    /**
     * 收货地址详情
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function addressdet(Request $request){
        $params = $request->only(['id']);
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $data = UserAddress::select(['id','province','city','district','address','contact_name','contact_phone','is_default'])->find($params['id']);

        return $this->formatAsjson($data);
    }

    /**
     * 收货地址列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function getaddress(){
        $data = UserAddress::select(['id','province','city','district','address','contact_name','contact_phone','is_default'])->get();

        return $this->formatAsjson($data);
    }

    /**
     * 修改默认地址
     * @param Request $request
     * @return array
     */
    public function updefault(Request $request){
        $params = $request->only(['id']);
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $users = $this->guard()->user();
        DB::table('user_addresses')->where([['user_id','=',$users->id],['is_default','=',1]])->update(['is_default' => 0]);
        $result = DB::table('user_addresses')->where([['user_id','=',$users->id],['id','=',$params['id']]])->update(['is_default' => 1]);
        if ($result){
            return $this->errorHandler(1009);
        }else{
            return $this->errorHandler(1010);
        }
    }

    /**
     * 删除收货地址
     * @param Request $request
     * @return array
     */
    public function deladdress(Request $request){
        $params = $request->only(['id']);
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $result = UserAddress::destroy($params['id']);
        if ($result){
            return $this->errorHandler(1007);
        }else{
            return $this->errorHandler(1008);
        }
    }


}
