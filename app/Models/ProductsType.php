<?php

namespace App\Models;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ProductsType extends Model
{
    use SoftDeletes;
    protected $table = 'products_type';
    protected $fillable = ['title'];


    protected static function ispre($id){
        $result = Product::where('type',$id)->first();
        if ($result){
            return true;
        }else{
            return false;
        }
    }

    protected static function gettype(){
        $result = ProductsType::select('id','title as name')->get()->toArray();
        $result2 = [];
        foreach ($result as $k => $v){
            $result2[$v['id']] = $v['name'];
        }
        return $result2;
    }

}
