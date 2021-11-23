<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CDRBC0Export;
use Monolog\Handler\NullHandlerTest;
use Illuminate\Support\Facades\Auth;
use App\Models\Cdrsachv2;
use Illuminate\Support\Facades\Session;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('phpbb3');
    }

    // [ 業績-cdrbc0 ]
    public function cdrbc0( $yymm = null )
    {
        if( $yymm == null ){
            $yymm = date('Ym'); // 202111
        }

        $last_yymm = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('-1 year'))->format('Ym');
        $yymm_last = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('-1 month'))->format('Ym');
        $yymm_next = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('1 month'))->format('Ym');
        $t_time = DB::table('autorun')->where('p_no','=','CDRBC2')->first();
        $parms = array('yymm'=>$yymm, 'yymm_last'=>$yymm_last, 'yymm_next'=>$yymm_next ,'t_time'=>$t_time->t_time );

        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];

        $cdrsachv_depno = Cdrsachv2::getsalesbydepno($yymm);
        $cdrsachv_psr   = Cdrsachv2::getsalesbypsr($yymm , $last_yymm ,$ldepno);
        // dd($cdrsachv_depno, $cdrsachv_psr);
        
        return view('cdrbc0', ['parms' => $parms, 'cdrsachv_depno' => $cdrsachv_depno, 'cdrsachv_psr' => $cdrsachv_psr,]);
    }
    // [ 業績 ] : export Excel
    public function cdrbc0_excel()
    {
        return Excel::download(new CDRBC0Export, 'cdrbc0.xlsx');
    }
    // [ 單品-cdrbb0 ]
    public function cdrbb0( $yymm = null )
    {
        if( $yymm == null ){
            $yymm = date('Ym'); // 202111
        }

        $yymm_last = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('-1 month'))->format('Ym');
        $yymm_next = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('1 month'))->format('Ym');

        $parms = array('yymm'=>$yymm, 'yymm_last'=>$yymm_last, 'yymm_next'=>$yymm_next );

        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = substr($ldepno,0,2).'%';

        $psr = Session::get('mancode');

        if ( substr($ldepno,0,2) == 'OT'){
            $sql = "SELECT cdrsal.itnbr, invmas.itdsc_utf8, invmas.spdsc_utf8, sum(qty) as qty, sum(bakqty) as bakqty, sum(shpamt) as shpamt, sum(bakamt) as bakamt FROM cdrsal"
            ." INNER JOIN cdrb96_ot ON cdrb96_ot.itnbr = cdrsal.itnbr "
            ." INNER JOIN invmas ON invmas.itnbr = cdrsal.itnbr where facno ='SBM' and yymm = '$yymm'"
            ." Group by cdrsal.itnbr ,invmas.itdsc_utf8, invmas.spdsc_utf8";
        } else {
            $sql = "SELECT cdrsal.itnbr, invmas.itdsc_utf8, invmas.spdsc_utf8,"
            .  "sum(IF(mancode='$psr', shpamt, 0) ) as psr_shpamt , sum(IF(mancode='$psr', bakamt, 0) ) as psr_bakamt , sum(shpamt) as shpamt , sum(bakamt) as bakamt ,"
            ." sum(IF(mancode='$psr', qty, 0) ) as psr_qty , sum(IF(mancode='$psr', bakqty, 0) ) as psr_bakqty , sum(qty) as qty , sum(bakqty) as bakqty FROM cdrsal"
            ." INNER JOIN cdrb96 ON cdrb96.itnbr = cdrsal.itnbr "
            ." INNER JOIN invmas ON invmas.itnbr = cdrsal.itnbr where facno ='SBM' and yymm = '$yymm' and cdrb96.special = 'Y' and cdrsal.dept like '$ldepno' "
            ." Group by cdrsal.itnbr ,invmas.itdsc_utf8,invmas.spdsc_utf8";
        }

        $cdrbb0 = DB::select(DB::raw($sql));
        // dd($cdrbb0);
        
        return view('cdrbb0', ['parms' => $parms, 'cdrbb0' => $cdrbb0]);
    }
    // [ 單品 ] : 業務單品明細排行 API | GET
    public function cdrb96( $yymm, $itnbr )
    {
        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = substr($ldepno, 0, 2).'%';

        $cdrb96 = DB::table('cdrsal')
            ->where('facno', '=', 'SBM')
            ->where('yymm', '=', $yymm)
            ->where('dept', 'like', $ldepno)
            ->where('itnbr', '=', $itnbr)
            ->join('secuser', 'secuser.userno', '=', 'cdrsal.mancode')
            ->select('secuser.userno', 'secuser.username_utf8', 'cdrsal.qty', 'cdrsal.bakqty', 'cdrsal.shpamt', 'cdrsal.bakamt')
            ->orderby('cdrsal.qty', 'desc')
            ->get();
        $invmas_name = DB::table('invmas')
            ->select('itnbr', 'itdsc_utf8', 'spdsc_utf8')
            ->where('itnbr', '=', $itnbr)
            ->get();
        
        $cdrb96_data = [];    
        foreach($cdrb96 as $key => $item){
            $cdrb96_data[$key]['userno'] = $item->userno;
            $cdrb96_data[$key]['username_utf8'] = $item->username_utf8;
            $cdrb96_data[$key]['qty'] = $item->qty;
            $cdrb96_data[$key]['bakqty'] = $item->bakqty;
            $cdrb96_data[$key]['shpamt'] = $item->shpamt;
            $cdrb96_data[$key]['bakamt'] = $item->bakamt;
            foreach($invmas_name as $val){
                $cdrb96_data[$key]['itnbr'] = $val->itnbr;
                $cdrb96_data[$key]['itdsc_utf8'] = $val->itdsc_utf8;
                $cdrb96_data[$key]['spdsc_utf8'] = $val->spdsc_utf8;
            }
        }
        // dd($cdrb96_data);
        // dd($invmas_name);
        // dd($cdrb96);
    
        // makeJson 參數位置對應順序
        if(isset($cdrb96_data) && count($cdrb96_data) > 0){ 
            return $this->makeJson(1, ['cdrb96_data' => $cdrb96_data], '成功得到資料'); 
        }else{
            return redirect('cdrbb0');
            // return $this->makeJson(0, null, '找不到資料'); 
        }
    }

    // [ 訂單-cdrba0 ] : 表單 POST
    public function cdrba0(Request $request)
    {
        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = $ldepno.'%';
        $mancode = Session::get('mancode');
        $psr_type = DB::table('cdr_telcode')->where('mancode',$mancode )->first(); // 檢查是否為主管
        // dd( Auth::user(), $ldepno, $mancode, $psr_type );

        // if "不是" 業務 || if 不等於 'PSR' 即為 "主管" : 下拉式選單可以選取到全部的人員
        if( empty($psr_type) || ($psr_type->type != 'PSR')){
            $getSecuser = DB::table('secuser')
                ->select(DB::raw("userno , CONCAT( pdepno , '[' , userno , ']' , username_utf8) AS userno_username"))
                ->where('pdepno', 'like', $ldepno)
                ->where('pdepno', '<>', '')
                ->whereNotIn('pdepno', ['001', 'ACT', 'CS', 'MIS', 'MT', 'AP', 'HUM', 'STO', 'DPN', 'DPS', 'DPC'])
                ->orderby('pdepno', 'asc')
                ->orderby('userno', 'asc')
                ->orderby('username_utf8', 'asc')
                ->get(); // 取出為陣列
            $secuser = array();
            foreach($getSecuser as $item){
                $secuser[$item->userno] = $item->userno_username;
            }
        } else {
            $getSecuser = DB::table('secuser')
                ->select(DB::raw("userno , CONCAT( pdepno , '[' , userno , ']' , username_utf8) AS userno_username"))
                ->where( 'userno', '=', $mancode )
                ->first(); // 取出為物件
            $secuser = array();
            $secuser[$getSecuser->userno] =  $getSecuser->userno_username;
        }
        // dd( Auth::user(), $ldepno, $mancode, $psr_type, $secuser, Auth::user()['username_utf8'] );
        
        $inputStart = $request->input('start');
        $inputEnd = $request->input('end');
        $inputPsr = $request->input('inputPsr');
        // dd($inputStart,  $inputEnd);

        // POST | if 有設定條件並按下 "查詢" 鈕
        if( $inputStart && $inputEnd && $inputPsr ){
            $cdrhad = DB::table('cdrhad')
                ->where('mancode', '=', $inputPsr)
                ->whereBetween('shpdate', array($inputStart, $inputEnd))
                ->get();

            $total_totamts  = 0;
            $total_tnfamt   = 0;
            foreach($cdrhad as $key => $row){
                switch ($row->hmark4) {
                    case '1':
                        $paydsc = '現金';
                        break;
                    case '2':
                        $paydsc = '劃撥';
                        break;
                    case '3':
                        $paydsc = '刷卡';
                        break;
                    case '4':
                        $paydsc = '已 收';
                        break;
                    case '5':
                        $paydsc = '宅配收款';
                        break;
                    case '6':
                        $paydsc = '貨到收款';
                        break;
                    default:
                        $paydsc = "ZZ";
                }

                $tnfamt  = DB::table('armtnf') // 取得帳款
                    ->select(DB::raw('SUM(tnfamts) as getTnfamts'))
                    ->where('cusno', '=', $row->cusno)
                    ->where('hadno', '=', $row->shpno)
                    ->get(); // 注意取出的資料型態為 "陣列包物件"
                $tnfamt = $tnfamt[0]->getTnfamts;

                if (substr($row->depno,0,2) == 'OT'){
                    $cdrscus = DB::table('cdrscus_otc')->where('cusno', $row->cusno)->where('trseq', $row->shptrseq)->first();
                }else{
                    $cdrscus = DB::table('cdrscus')->where('cusno', $row->cusno)->where('trseq', $row->shptrseq)->first();
                }

                $miscode =DB::table('miscode')->where('ckind','GD')->where('code',$row->sndcode)->first();
                $cdrhosp =DB::table('cdr_hosp')->where('hos_no',$row->cuycode)->first();

                $total_tnfamt  += $tnfamt;
                $total_totamts += $row->totamts;

                // 重新組裝 $cdrhad
                $cdrhad[$key]->tnfamt = number_format($tnfamt, 0, ',', ',');
                $cdrhad[$key]->totamts = number_format($row->totamts, 0, ',', ',');
                $cdrhad[$key]->paycode = $paydsc;
                $cdrhad[$key]->sndcode = $miscode->cdesc_utf8;
                $cdrhad[$key]->cuycode = (null == $cdrhosp ? '' : $cdrhosp->hospname_utf8);
                if($cdrscus) {
                    $cdrhad[$key]->cusna = $cdrscus->cusna_utf8;
                    $cdrhad[$key]->tel = $cdrscus->tel;
                    $cdrhad[$key]->address = $cdrscus->address1_utf8.$cdrscus->address2_utf8;
                }else{
                    $cdrhad[$key]->cusna = '';
                    $cdrhad[$key]->tel = '';
                    $cdrhad[$key]->address = '';
                }
            }
            // dd($cdrhad);

            return view('cdrba0')
                ->with('cdrhad', $cdrhad)
                ->with('psrs', $secuser)
                ->with('selected_psr', $inputPsr) // 帶出 select 框的 option value 值: 'S102'
                ->with('total', array('tnfamt'=>number_format($total_tnfamt, 0, ',', ','), 'totamts'=>number_format($total_totamts, 0, ',', ',')))
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));

        } else {  // GET | 純觀看內容、沒有按下 "查詢"

            $inputStart = Carbon::now()->addDays(-7)->toDateString();
            $inputEnd = Carbon::now()->toDateString();
            // dd( Carbon::now()->addDays(-7)->toDateString() ); // 2021-11-04
            // dd( Carbon::now()->toDateString() ); // 2021-11-11
            return view('cdrba0')
                ->with('psrs', $secuser)
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));
        }
    }
    // [ 訂單 ] : 業務訂單/出貨明細 API | GET
    public function cdrdta( $shpno )
    {
        $cdrdta =  DB::table('cdrdta')
            ->where('shpno', '=', $shpno)
            ->join('invmas', 'cdrdta.itnbr', '=', 'invmas.itnbr')
            ->select('cdrdta.itnbr','cdrdta.shpqy1','cdrdta.shpamts','invmas.itdsc_utf8','invmas.spdsc_utf8')
            ->get();
        foreach($cdrdta as $item){
            $item->shpqy1 = number_format($item->shpqy1, 0, ',', ',');
            $item->shpamts = number_format($item->shpamts, 0, ',', ',');
        }
        
        // dd( $cdrdta );
        if(isset($cdrdta) && count($cdrdta) > 0){ 
            return $this->makeJson(1, ['cdrdta' => $cdrdta], '成功得到資料'); 
        }else{
            return redirect('cdrba0');
            // return $this->makeJson(0, null, '找不到資料'); 
        }
    }

    // [ 展示 ] : 展示說明會統計
    public function cdrbd0( $yymm = null )
    {
        if( $yymm == null ){
            $yymm = date('Ym'); // 202111
        }
        $date_end  = date_add(date_create($yymm .'01' ),date_interval_create_from_date_string('1 month'))->format('Y-m-d');
        $date_start= date_create($yymm.'01' )->format('Y-m-d');
        $yymm_last = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('-1 month'))->format('Ym');
        $yymm_next = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('1 month'))->format('Ym');

        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = substr($ldepno, 0, 2) . '%';
        // dd($yymm, $date_end, $date_start, $yymm_last, $yymm_next, Auth::user(), $ldepno);

        $sql = "select pdepno , mandsc , sum(cd5) as cd5 , sum(cd3) as cd3 from ( "
            ."select eip200_f00, pdepno as pdepno , CONCAT(eip200_f12,username_utf8) as mandsc , 1 as cd5 , 0 as cd3 FROM eip200_t01 "
            ."INNER JOIN secuser ON eip200_t01.eip200_f12 = secuser.userno "
            ."where eip200_f01 = 'A' and eip200_f02 >='$date_start' and eip200_f02 < '$date_end' and pdepno like '$ldepno' and eip200_f17 in('B','E','H') "
            ." union "
            ."select cdrno,depno as pdepno ,CONCAT(mancode, username_utf8) as mandsc ,0 as cd5,1 as cd3 from cdrhmas_sap "
            ."INNER JOIN secuser ON cdrhmas_sap.mancode = secuser.userno "
            ."where act_date >='$date_start' and  act_date <'$date_end' and pdepno like '$ldepno' and act_type ='展售會' and smcfm = 'y' ) as cdr_cd3_cd5 "
            ."group by pdepno,mandsc with ROLLUP ";
        $cdrbd0 = DB::select(DB::raw($sql));
        // dd($cdrbd0);

        $parms = array('yymm'=>$yymm, 'yymm_last'=>$yymm_last, 'yymm_next'=>$yymm_next);
        return view('cdrbd0')->with('cdrbd0', $cdrbd0)->with('parms', $parms);
    }
    // [ 展示 ] : 展示會明細 API | GET
    public function cdrbd0_cdrhad($yymm, $mandsc)
    {
        $date_end  = date_add(date_create($yymm.'01' ),date_interval_create_from_date_string(" 1 month"))->format('Y-m-d');
        $date_start= date_create($yymm.'01' )->format('Y-m-d');
        $cdrhmas_sap = DB::table('cdrhmas_sap')
            ->where('act_date','>=',$date_start)
            ->where('act_date','<',$date_end)
            ->where('act_type', '=','展售會')
            ->where('smcfm', '=', 'y')
            ->where('cdrhmas_sap.mancode','=',substr($mandsc,0,4))
            ->leftjoin('cdr_hosp', 'cdrhmas_sap.hos_no', '=', 'cdr_hosp.hos_no')
            ->select('act_type','act_date','cdrno','cdrhmas_sap.hos_no','firm_name_utf8','cdr_hosp.hospname_utf8')
            ->orderBy('act_date','ASC')
            ->get();

        // dd( $cdrhmas_sap );
        if(isset($cdrhmas_sap) && count($cdrhmas_sap) > 0){ 
            return $this->makeJson(1, ['cdrhmas_sap' => $cdrhmas_sap], '成功得到資料'); 
        }else{
            // return redirect('cdrbd0');
            return $this->makeJson(0, null, '找不到資料'); 
        }
    }
    // [ 展示 ] : 說明會明細 API | GET
    public function cdrbd0_eip($yymm, $mandsc)
    {
        $date_end  = date_add(date_create($yymm.'01' ),date_interval_create_from_date_string(" 1 month"))->format('Y-m-d');
        $date_start= date_create($yymm.'01' )->format('Y-m-d');
        $eip200 = DB::table('eip200_t01')
            ->where('eip200_f02','>=',$date_start)
            ->where('eip200_f02','<',$date_end)
            ->where('eip200_f01', '=','A')
            ->whereIN('eip200_f17',['B','E','H'])
            ->where('eip200_f12','=',substr($mandsc,0,4))
            ->leftjoin('cdr_hosp', 'eip200_t01.eip200_f05', '=', 'cdr_hosp.hos_no')
            ->select('eip200_f00','eip200_f02','eip200_f07','eip200_f09','cdr_hosp.hospname_utf8')
            ->orderBy('eip200_f02','ASC')
            ->get();
        
        // dd( $eip200 );
        if(isset($eip200) && count($eip200) > 0){ 
            return $this->makeJson(1, ['eip200' => $eip200], '成功得到資料'); 
        }else{
            // return redirect('cdrbd0');
            return $this->makeJson(0, null, '找不到資料'); 
        }
    }

    // [ 業務新客戶統計 ]
    public function cdrbe0( $yymm = null )
    {
        if( $yymm == null ){
            $yymm = date('Ym'); // 202111
        }
        $date_end  = date_add(date_create($yymm .'01' ),date_interval_create_from_date_string('1 month'))->format('Y-m-d');
        $date_start= date_create($yymm.'01' )->format('Y-m-d');
        $yymm_last = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('-1 month'))->format('Ym');
        $yymm_next = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('1 month'))->format('Ym');
        $parms = array('yymm'=>$yymm, 'yymm_last'=>$yymm_last, 'yymm_next'=>$yymm_next);

        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = substr($ldepno, 0, 2) . '%';
        // dd($yymm, $date_end, $date_start, $yymm_last, $yymm_next, Auth::user(), $ldepno);

        $sql = "select secuser.pdepno, concat(mancode,'',username_utf8) as mandsc , count(*) as qty from cdrcus ,secuser"
        ." where indate >='$date_start' and indate <'$date_end' and cdrcus.mancode = secuser.userno "
        ." and secuser.pdepno like '$ldepno' "
        ." group by secuser.pdepno, mandsc with ROLLUP ";

        $cdrbe0 = DB::select(DB::raw($sql));
        // dd($cdrbe0);

        return view('cdrbe0')->with('cdrbe0', $cdrbe0)->with('parms',$parms);
    }

    // [ OTC 連鎖客戶業績統計 ]
    public function cdrca0( $yymm = null )
    {
        if( $yymm == null ){
            $yymm = date('Ym'); // 202111
        }
        // $date_end  = date_add(date_create($yymm .'01' ),date_interval_create_from_date_string('1 month'))->format('Y-m-d');
        // $date_start= date_create($yymm.'01' )->format('Y-m-d');
        $last_yymm = date_add(date_create($yymm.'01' ),date_interval_create_from_date_string("-1 year"))->format('Ym');
        $yymm_last = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('-1 month'))->format('Ym');
        $yymm_next = date_add(date_create($yymm . '01'), date_interval_create_from_date_string('1 month'))->format('Ym');
        $parms = array('yymm'=>$yymm, 'yymm_last'=>$yymm_last, 'yymm_next'=>$yymm_next);

        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = substr($ldepno, 0, 2) . '%';
        // dd($yymm, $date_end, $date_start, $yymm_last, $yymm_next, Auth::user(), $ldepno);

        $sql = "SELECT yymm, cdrotc_target.agentfacno, target, cdrcus_ot4.cusna_utf8 from cdrotc_target "
             . "INNER JOIN cdrcus_ot4 ON cdrotc_target.agentfacno = cdrcus_ot4.agentfacno where yymm = $yymm ";
        $cdrca0 = DB::select(DB::raw($sql)); // 各連鎖客戶的 "業績目標"
        // dd($cdrca0);

        $return_data = array();
        $sum_target = 0;    // 總表合計 SUM : 累積目標
        $sum_sales = 0;     // 總表合計 SUM : 累計業績
        $sum_bakamt = 0;    // 總表合計 SUM : 累計退貨
        $sum_lastsales = 0; // 總表合計 SUM : 去年同期
        foreach ($cdrca0 as $row) {
            $sql_sales = "SELECT sum(eis_cusitnbr_otc.shpamt) as shpamt ,sum(eis_cusitnbr_otc.bakamt) as bakamt "
                        . "FROM eis_cusitnbr_otc "
                        . "INNER JOIN otc_cdrcus_agent ON eis_cusitnbr_otc.cusno = otc_cdrcus_agent.cusno "
                        . "INNER JOIN cdrcus_otc ON eis_cusitnbr_otc.cusno = cdrcus_otc.cusno "
                        . "WHERE eis_cusitnbr_otc.yymm = '$yymm' and  otc_cdrcus_agent.agentfacno = '$row->agentfacno' ";
            $cdrca0_sales = DB::select(DB::raw($sql_sales)); // 各連鎖客戶的 "累計退貨"、"累計業績"
            // dd($cdrca0, $cdrca0_sales);

            $sql_lastsales = "SELECT sum(eis_cusitnbr_otc.shpamt) as shpamt ,sum(eis_cusitnbr_otc.bakamt) as bakamt FROM eis_cusitnbr_otc "
                . "INNER JOIN otc_cdrcus_agent ON eis_cusitnbr_otc.cusno = otc_cdrcus_agent.cusno "
                . "INNER JOIN cdrcus_otc ON eis_cusitnbr_otc.cusno = cdrcus_otc.cusno "
                . "WHERE eis_cusitnbr_otc.yymm = '$last_yymm' and  otc_cdrcus_agent.agentfacno = '$row->agentfacno' ";
            $cdrca0_lastsales = DB::select(DB::raw($sql_lastsales)); // 各連鎖客戶 "去年同期" 的 "累計退貨"、"累計業績"
            // dd($cdrca0, $cdrca0_sales[0], $cdrca0_lastsales);

            $sum_target += $row->target;
            $sum_sales += ($cdrca0_sales[0]->shpamt + $cdrca0_sales[0]->bakamt ) ;
            $sum_bakamt += $cdrca0_sales[0]->bakamt;
            $sum_lastsales += ($cdrca0_lastsales[0]->shpamt + $cdrca0_lastsales[0]->bakamt) ;
            array_push( $return_data, (object)array(
                'yymm' => $row->yymm,
                'cusna' => $row->cusna_utf8,
                'agentfacno' => $row->agentfacno,
                'target' => number_format($row->target, 0, ',', ','), // 業績目標
                'sales' => number_format($cdrca0_sales[0]->shpamt + $cdrca0_sales[0]->bakamt, 0, ',', ','), // 累計業績
                'shpamt' => number_format($cdrca0_sales[0]->shpamt, 0, ',', ','),
                'bakamt' => number_format($cdrca0_sales[0]->bakamt, 0, ',', ','),
                'lastsales' => number_format($cdrca0_lastsales[0]->shpamt + $cdrca0_lastsales[0]->bakamt, 0, ',', ','), // 去年同期
                'rate' => number_format($row->target > 0 ? ($cdrca0_sales[0]->shpamt + $cdrca0_sales[0]->bakamt) / $row->target * 100 : 0,2,'.',',').'%', // 達成率
                'rate2' => number_format(($cdrca0_lastsales[0]->shpamt + $cdrca0_lastsales[0]->bakamt) > 0 ? (($cdrca0_sales[0]->shpamt + $cdrca0_sales[0]->bakamt) / ($cdrca0_lastsales[0]->shpamt + $cdrca0_lastsales[0]->bakamt) - 1 )* 100 : 0,2,'.',',').'%',
            ));
        }
        // dd($cdrca0_sales[0], $cdrca0_lastsales, $return_data);

        array_push( $return_data, (object)array(
            'yymm' => $yymm,
            'cusna' => '合 計 : ',
            'agentfacno' => 'SUM',
            'target' => number_format($sum_target, 0, ',', ','),
            'sales' => number_format($sum_sales, 0, ',', ','),
            'bakamt' => number_format($sum_bakamt, 0, ',', ','),
            'lastsales' => number_format($sum_lastsales, 0, ',', ','),
            'rate' => number_format($sum_target > 0 ? ($sum_sales) / $sum_target * 100 : 0,2,'.',',').'%',
            'rate2' => number_format(($sum_lastsales ) > 0 ? (( $sum_sales/ $sum_lastsales) - 1 )* 100 : 0,2,'.',',').'%',
        ));

        // dd($return_data);
        return view('cdrca0')->with('cdrca0', $return_data)->with('parms',$parms);
    }

    // 用來生成 JSON 字串 
    private function makeJson($status, $data=null, $msg=null)
    {
        return response()->json(['status' => $status, 'message' => $msg, 'data' => $data])       
            ->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}

