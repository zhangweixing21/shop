<?php

namespace App\Http\Controllers\V1;

use App\Exceptions\InvalidRequestException;
use App\Models\ProductsType;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class ProductsController extends AuthController
{
    /**
     * 分类
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function category(Request $request){
        $limit = 20;
        $page = $request->input('page', 1);
        $start = ($page-1) * $limit;
        $where = [];
        if (empty($request->input('type_id')) && $request->input('type_id')){
            $where['id'] = $request->input('type_id');
        }

        $result = ProductsType::query()
            ->with(['cate_products:id,title,image']) // 预先加载关联关系
            ->select(['products_type.id as type_id','products_type.title as type_title'])
            ->where($where)
            ->offset($start)
            ->limit($limit)
            ->get();

        return $this->formatAsjson($result);
    }

    /**
     * 商品详情
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function detail(Request $request){
        $params = $request->only('id');
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $product = Product::select(['id','title','description','image','rating','sold_count','review_count','on_sale','price'])->find($params['id']);

        if ($product->on_sale == 0) {
            return $this->errorHandler(1003);
        }
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku']) // 预先加载关联关系
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at') // 筛选出已评价的
            ->orderBy('reviewed_at', 'desc') // 按评价时间倒序
            ->limit(10) // 取出 10 条
            ->get();

        $favored = 0;
        $users = $this->guard()->user();
        // 从当前用户已收藏的商品中搜索 id 为当前商品 id 的商品
        $favorite_products = DB::table('user_favorite_products')->where(['product_id' => $params['id'],'user_id' => $users->id])->value('id');
        $favored = $favorite_products ? 1 : 0;
        $data = [];
        $data['product'] = $product;
        $data['reviews'] = $reviews;
        $data['favored'] = $favored;

        return $this->formatAsjson($data);
    }

    /**
     * 收藏
     * @param Request $request
     * @return array
     */
    public function favor(Request $request){
        $params = $request->only('id');
        if (!isset($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }
        $users = $this->guard()->user();
        $favorite_products = DB::table('user_favorite_products')->where(['product_id' => $params['id'],'user_id' => $users->id])->value('id');
        if ($favorite_products){
            return $this->errorHandler(1004);
        }
        $result = DB::insert('insert into user_favorite_products (user_id,product_id,created_at,updated_at) values (?,?,?,?)', [$users->id,$params['id'],date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);
        if ($result){
            return $this->errorHandler(1005);
        }else{
            return $this->errorHandler(1006);
        }

    }

    /**
     * 删除收藏
     * @param Request $request
     * @return array
     */
    public function disfavor(Request $request){
        $params = $request->only('id');
        if (!is_array($params['id']) && empty($params['id'])){
            return $this->errorHandler(1000);
        }

        $users = $this->guard()->user();
        foreach ($params['id'] as $k => $v){
            $result = DB::table('user_favorite_products')->where(['user_id' => $users->id,'product_id' => $v])->delete();
        }
        if ($result){
            return $this->errorHandler(1007);
        }else{
            return $this->errorHandler(1008);
        }

    }

    /**
     * 我的收藏
     * @return \Illuminate\Http\JsonResponse
     */
    public function myfavor(){
        $users = $this->guard()->user();
        $favorite_products = DB::table('user_favorite_products as a')
            ->select(['a.id','a.product_id','b.title','b.image','b.price'])
            ->leftJoin('products as b','a.product_id','=','b.id')
            ->where('user_id',$users->id)
            ->get();

        return $this->formatAsjson($favorite_products);
    }

}
