<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Cdrsachv2 extends Model
{
    use HasFactory;
    protected $table = 'cdrsachv2';

    public static function getsalesbydepno($yymm)
    {
        $sql = "SELECT substr(depno,1,2) as pdepno , sum(sales_s) as cp_sales_s ,sum(sales_o) as cp_sales_o , sum(sales_c) as cp_sales_c, sum(sales_v) as cp_sales_v, sum(sales_h) as cp_sales_h, sum(sales_a) as cp_sales_a, sum(sales_b) as cp_sales_b, sum(sales_r) as cp_sales_r,"
            ."sum(sales_all) as cp_sales_all , sum(sales_bak) as cp_sales_bak ,sum(sales_u) as cp_sales_u,sum(target) as cp_target,sum(sales_day) as cp_sales_day "
            ."FROM cdrsachv2 where yymm = '$yymm' group by substr(depno,1,2) "
            ."UNION "
            ."SELECT 'Total' as pdepno , sum(sales_s) as cp_sales_s ,sum(sales_o) as cp_sales_o , sum(sales_c) as cp_sales_c, sum(sales_v) as cp_sales_v, sum(sales_h) as cp_sales_h, sum(sales_a) as cp_sales_a, sum(sales_b) as cp_sales_b, sum(sales_r) as cp_sales_r,"
            ."sum(sales_all) as cp_sales_all , sum(sales_bak) as cp_sales_bak ,sum(sales_u) as cp_sales_u,sum(target) as cp_target,sum(sales_day) as cp_sales_day "
            ."FROM cdrsachv2 where yymm = '$yymm'";

        $cdrsalchv2_depno = DB::select(DB::raw($sql));
      
        $return_data = array();
        foreach ($cdrsalchv2_depno as $row) {
            if($yymm >='201810'){ 
                array_push($return_data, (object)array(
                    'pdepno' => $row->pdepno,
                    'SALES_S' => number_format($row->cp_sales_s), // 使用 number_format 來千分位格式化數字
                    'SALES_O' => number_format($row->cp_sales_o),
                    'SALES_C' => number_format($row->cp_sales_c),
                    'SALES_V' => number_format($row->cp_sales_v), 
                    'SALES_SU' => number_format($row->cp_sales_c + $row->cp_sales_u),
                    'SALES_BAK' => number_format($row->cp_sales_bak),
                    'SALES' => number_format($row->cp_sales_all - $row->cp_sales_bak),
                    'TARGET' => number_format($row->cp_target),
                    'PERC2' => number_format($row->cp_target > 0 ? ($row->cp_sales_all - $row->cp_sales_bak) / $row->cp_target * 100 : 0,2,'.',',').'%',
                    'SALES_U' => number_format($row->cp_sales_u),
                    'SALES_H' => number_format($row->cp_sales_h),
                    'SALES_A' => number_format($row->cp_sales_a),
                    'SALES_B' => number_format($row->cp_sales_b),
                    'SALES_R' => number_format($row->cp_sales_r),
                    'SALES_DAY' => number_format($row->cp_sales_day),
                ));
            } else {
                array_push($return_data, (object)array(
                    'pdepno' => $row->pdepno,
                    'SALES_S' => number_format($row->cp_sales_s),
                    'SALES_O' => number_format($row->cp_sales_o),
                    'SALES_C' => number_format($row->cp_sales_c),
                    'SALES_V' => number_format($row->cp_sales_v),
                    'SALES_SU' => number_format($row->cp_sales_c + $row->cp_sales_u),
                    'SALES_BAK' => number_format($row->cp_sales_bak),
                    'SALES' => number_format($row->cp_sales_all + $row->cp_sales_c + $row->cp_sales_u - $row->cp_sales_bak),
                    'TARGET' => number_format($row->cp_target),
                    'PERC2' => number_format($row->cp_target > 0 ? ($row->cp_sales_all + $row->cp_sales_c + $row->cp_sales_u - $row->cp_sales_bak) / $row->cp_target * 100 : 0,2,'.','') . '%',
                    'SALES_U' => number_format($row->cp_sales_u),
                    'SALES_H' => number_format($row->cp_sales_h),
                    'SALES_A' => number_format($row->cp_sales_a),
                    'SALES_B' => number_format($row->cp_sales_b),
                    'SALES_R' => number_format($row->cp_sales_r),
                    'SALES_DAY' => number_format($row->cp_sales_day),
                ));
            }
        }
        // return $cdrsalchv2_depno;
        return $return_data;
    }

    public static function getsalesbypsr($yymm , $last_yymm , $ldepno)
    {
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = substr($ldepno,0,2).'%';

        $sql = "select * from (SELECT yymm,depno,'' as mancode,substr(depno,1,2) as fdepno,concat(depno,'[',count(mancode),'] : sum') as mandsc,  sum(sales_s) as cp_sales_s ,sum(sales_o) as cp_sales_o , sum(sales_c) as cp_sales_c, sum(sales_v) as cp_sales_v, sum(sales_h) as cp_sales_h, sum(sales_a) as cp_sales_a, sum(sales_b) as cp_sales_b, sum(sales_r) as cp_sales_r, "
        ."sum(sales_all) as cp_sales_all , sum(sales_bak) as cp_sales_bak ,sum(sales_u) as cp_sales_u,sum(target) as cp_target,sum(sales_day) as cp_sales_day "
        ."FROM cdrsachv2 , secuser	where yymm ='$yymm' and depno like '$ldepno' and mancode = secuser.userno group by yymm, depno "
        ."UNION "
        ."(SELECT yymm,depno,mancode,substr(depno,1,2) as fdepno, concat(mancode,secuser.username_utf8) as mandsc,  sales_s as cp_sales_s ,sales_o as cp_sales_o , sales_c as cp_sales_c, sales_v as cp_sales_v, sales_h as cp_sales_h, sales_a as cp_sales_a, sales_b as cp_sales_b, sales_r as cp_sales_r, "
        ."sales_all as cp_sales_all , sales_bak as cp_sales_bak ,sales_u as cp_sales_u,target as cp_target,sales_day as cp_sales_day "
        ."FROM cdrsachv2 , secuser where (yymm ='$yymm' or yymm = '$last_yymm' ) and pdepno like '$ldepno' and mancode = secuser.userno order by depno,mancode ) ) as cdrsachv order by depno,mancode,yymm desc";

        $cdrsalchv2_psr = DB::select(DB::raw($sql));

        $return_data = array();
        foreach ($cdrsalchv2_psr as $row) {
            if($yymm >='201810'){
                array_push( $return_data, (object)array(
                    'yymm' => $row->yymm,
                    'fdepno' => $row->fdepno,
                    'depno' => $row->depno,
                    'mancode' => $row->mancode,
                    'mandsc' => $row->mandsc,
                    'SALES_S' => number_format($row->cp_sales_s, 0, ',', ','),
                    'SALES_O' => number_format($row->cp_sales_o, 0, ',', ','),
                    'SALES_C' => number_format($row->cp_sales_c, 0, ',', ','),
                    'SALES_V' => number_format($row->cp_sales_v, 0, ',', ','),
                    'SALES_SU' => number_format($row->cp_sales_c + $row->cp_sales_u, 0, ',', ','),
                    'SALES_BAK' => number_format($row->cp_sales_bak, 0, ',', ','),
                    'SALES' => number_format($row->cp_sales_all - $row->cp_sales_bak, 0, ',', ','),
                    'TARGET' => number_format($row->cp_target, 0, ',', ','),
                    'PERC2' => number_format($row->cp_target > 0 ? ($row->cp_sales_all  - $row->cp_sales_bak) / $row->cp_target * 100 : 0,2,'.',',').'%',
                    'SALES_U' => number_format($row->cp_sales_u, 0, ',', ','),
                    'SALES_H' => number_format($row->cp_sales_h, 0, ',', ','),
                    'SALES_A' => number_format($row->cp_sales_a, 0, ',', ','),
                    'SALES_B' => number_format($row->cp_sales_b, 0, ',', ','),
                    'SALES_R' => number_format($row->cp_sales_r, 0, ',', ','),
                    'SALES_DAY' => number_format($row->cp_sales_day, 0, ',', ','),
                ));
            } else {
                array_push( $return_data, (object)array(
                    'yymm' => $row->yymm,
                    'fdepno' => $row->fdepno,
                    'depno' => $row->depno,
                    'mancode' => $row->mancode,
                    'mandsc' => $row->mandsc,
                    'SALES_S' => number_format($row->cp_sales_s, 0, ',', ','),
                    'SALES_O' => number_format($row->cp_sales_o, 0, ',', ','),
                    'SALES_C' => number_format($row->cp_sales_c, 0, ',', ','),
                    'SALES_V' => number_format($row->cp_sales_v, 0, ',', ','),
                    'SALES_SU' => number_format($row->cp_sales_c + $row->cp_sales_u, 0, ',', ','),
                    'SALES_BAK' => number_format($row->cp_sales_bak, 0, ',', ','),
                    'SALES' => number_format($row->cp_sales_all + $row->cp_sales_c + $row->cp_sales_u - $row->cp_sales_bak, 0, ',', ','),
                    'TARGET' => number_format($row->cp_target, 0, ',', ','),
                    'PERC2' => number_format($row->cp_target > 0 ? ($row->cp_sales_all + $row->cp_sales_c + $row->cp_sales_u - $row->cp_sales_bak) / $row->cp_target * 100 : 0,2,'.',',').'%',
                    'SALES_U' => number_format($row->cp_sales_u, 0, ',', ','),
                    'SALES_H' => number_format($row->cp_sales_h, 0, ',', ','),
                    'SALES_A' => number_format($row->cp_sales_a, 0, ',', ','),
                    'SALES_B' => number_format($row->cp_sales_b, 0, ',', ','),
                    'SALES_R' => number_format($row->cp_sales_r, 0, ',', ','),
                    'SALES_DAY' => number_format($row->cp_sales_day, 0, ',', ','),
                ));
            }
        }
        // return $cdrsalchv2_psr;
        return $return_data;
    }

    public static function exportsalesbydepno($yymm)
    {
        $sql = "SELECT substr(depno,1,2) as pdepno , sum(sales_s) as cp_sales_s ,sum(sales_o) as cp_sales_o , sum(sales_c) as cp_sales_c, sum(sales_v) as cp_sales_v, sum(sales_h) as cp_sales_h, sum(sales_a) as cp_sales_a, sum(sales_b) as cp_sales_b, sum(sales_r) as cp_sales_r,"
            ."sum(sales_all) as cp_sales_all , sum(sales_bak) as cp_sales_bak ,sum(sales_u) as cp_sales_u,sum(target) as cp_target,sum(sales_day) as cp_sales_day "
            ."FROM cdrsachv2 where yymm = '$yymm' group by substr(depno,1,2) "
            ."UNION "
            ."SELECT 'Total' as pdepno , sum(sales_s) as cp_sales_s ,sum(sales_o) as cp_sales_o , sum(sales_c) as cp_sales_c, sum(sales_v) as cp_sales_v, sum(sales_h) as cp_sales_h, sum(sales_a) as cp_sales_a, sum(sales_b) as cp_sales_b, sum(sales_r) as cp_sales_r,"
            ."sum(sales_all) as cp_sales_all , sum(sales_bak) as cp_sales_bak ,sum(sales_u) as cp_sales_u,sum(target) as cp_target,sum(sales_day) as cp_sales_day "
            ."FROM cdrsachv2 where yymm = '$yymm'";

        $cdrsalchv2_depno = DB::select(DB::raw($sql));
      
        $return_data = array();
        foreach ($cdrsalchv2_depno as $row) {
            if($yymm >='201810'){ 
                array_push($return_data, (object)array(
                    'pdepno' => $row->pdepno,
                    'SALES_DAY' => number_format($row->cp_sales_day),
                    'SALES_S' => number_format($row->cp_sales_s), // 使用 number_format 來千分位格式化數字
                    'SALES_O' => number_format($row->cp_sales_o),
                    'SALES_V' => number_format($row->cp_sales_v), 
                    'SALES_B' => number_format($row->cp_sales_b),
                    'SALES_R' => number_format($row->cp_sales_r),
                    'SALES_H' => number_format($row->cp_sales_h),
                    'SALES_A' => number_format($row->cp_sales_a),
                    'SALES_BAK' => number_format($row->cp_sales_bak),
                    'SALES' => number_format($row->cp_sales_all - $row->cp_sales_bak),
                    'TARGET' => number_format($row->cp_target),
                    'PERC2' => number_format($row->cp_target > 0 ? ($row->cp_sales_all - $row->cp_sales_bak) / $row->cp_target * 100 : 0,2,'.',',').'%',                   
                ));
            } else {
                array_push($return_data, (object)array(
                    'pdepno' => $row->pdepno,
                    'SALES_DAY' => number_format($row->cp_sales_day),
                    'SALES_S' => number_format($row->cp_sales_s),
                    'SALES_O' => number_format($row->cp_sales_o),
                    'SALES_V' => number_format($row->cp_sales_v),
                    'SALES_B' => number_format($row->cp_sales_b),
                    'SALES_R' => number_format($row->cp_sales_r),
                    'SALES_H' => number_format($row->cp_sales_h),
                    'SALES_A' => number_format($row->cp_sales_a),
                    'SALES_BAK' => number_format($row->cp_sales_bak),
                    'SALES' => number_format($row->cp_sales_all + $row->cp_sales_c + $row->cp_sales_u - $row->cp_sales_bak),
                    'TARGET' => number_format($row->cp_target),
                    'PERC2' => number_format($row->cp_target > 0 ? ($row->cp_sales_all + $row->cp_sales_c + $row->cp_sales_u - $row->cp_sales_bak) / $row->cp_target * 100 : 0,2,'.','') . '%',
                ));
            }
        }
        // return $cdrsalchv2_depno;
        return $return_data;
    }
}
