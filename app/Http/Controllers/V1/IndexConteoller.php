<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2020/5/25
 * Time: 14:01
 */

namespace App\Http\Controllers\V1;



use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\V1\Index;
use App\Exceptions\InvalidRequestException;
use Illuminate\Support\Str;

class IndexConteoller extends AuthController
{

    public function index(){
        $data = [];
        $index = new Index();
        $data['hot'] = $index->gethot();
        $data['boutique'] = $index->boutique();
        $data['banner'] = [];

        return $this->formatAsjson($data);
    }

    public function search(Request $request){
        // 创建一个查询构造器
        $builder = Product::query()->select(['id','title','image','sold_count','price'])->where('on_sale', true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if ($order = $request->input('order', '')) {
            // 是否是以 _asc 或者 _desc 结尾
            if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
                // 如果字符串的开头是这 3 个字符串之一，说明是一个合法的排序值
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        $limit =5;
        $page = $request->input('page', 1);
        $start = ($page-1)*$limit;
        $products = $builder->offset($start)->limit($limit)->get();
        foreach ($products as $k => $v){
            // 如果 image 字段本身就已经是完整的 url 就直接返回
            if (!Str::startsWith($v->image, ['http://', 'https://'])) {
                $products[$k]->image = \Storage::disk('public')->url($v->image);
            }
        }
        return $this->formatAsjson($products);
    }


}