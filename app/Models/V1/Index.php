<?php
/**
 * Created by PhpStorm.
 * User: Lenovo
 * Date: 2020/5/25
 * Time: 14:08
 */

namespace App\Models\V1;

use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

class Index extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function gethot(){
        $result = DB::table('products')->select(['id','title','image','price'])->where('is_hot',1)->orderBy('sold_count','desc')->limit(3)->get();
        foreach ($result as $k => $v){
            $result[$k]->image = $this->ImageUr($v->image);
        }

        return $result;
    }

    public function boutique(){
        $result = DB::table('products')->select(['id','title','image','price'])->where('is_boutique',1)->orderBy('sold_count','desc')->limit(9)->get();
        foreach ($result as $k => $v){
            $result[$k]->image = $this->ImageUr($v->image);
        }

        return $result;
    }

    public function ImageUr($image)
    {
        // 如果 image 字段本身就已经是完整的 url 就直接返回
        if (Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }
        return \Storage::disk('public')->url($image);
    }


}