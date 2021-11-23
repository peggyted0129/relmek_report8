<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('phpbb3');
    }

    //客戶查詢 - 依照購買品號
    public function itnbr( $yymm = null, Request $request )
    {  
        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = $ldepno.'%';
        $mancode = Session::get('mancode');
        $psr_type = DB::table('cdr_telcode')->where('mancode',$mancode )->first(); // 檢查是否為主管
        // dd( Auth::user(), $ldepno, $mancode, $psr_type );

        // if "不是" 業務
        if ( empty($psr_type) ){
            $get_cdr_telcode = DB::table('cdr_telcode')
                ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                ->where('cdr_telcode.pdepno', 'like', $ldepno)
                ->where('secuser.enabled', '=', 'Y')
                ->whereIn('cdr_telcode.pdepno', ['MNA','MNB','MNC','MND','MNE', 'MSA','MSB','MSC','MSD','MSE', 'MCA','MCB','MCC','MCD','MCE','TM'])
                ->orderby('cdr_telcode.pdepno', 'asc')
                ->orderby('userno_username', 'asc')
                ->orderby('telcode', 'asc')
                ->get();  // 取出為陣列包物件
          
            $cdr_telcode = array();
            foreach($get_cdr_telcode as $item){
                $cdr_telcode[$item->telcode] = $item->userno_username;
            }
           
            // dd($get_cdr_telcode, $cdr_telcode);
        } elseif( $psr_type->type <> 'PSR' ) { // 若為業務主管
            $get_cdr_telcode = DB::table('cdr_telcode')
                    ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                    ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                    ->where('cdr_telcode.pdepno', 'like', $ldepno)
                    ->orderby('cdr_telcode.pdepno', 'asc')
                    ->orderby('userno', 'asc')
                    ->get();  // 取出為陣列包物件
            $cdr_telcode = array();
            foreach($get_cdr_telcode as $item){
                $cdr_telcode[$item->telcode] = $item->userno_username;
            }      
            // dd($get_cdr_telcode, $cdr_telcode);
        } else { // 若為業務組員 ( $psr_type->type = 'PSR' )
            $get_cdr_telcode = DB::table('cdr_telcode')
                ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                ->where( 'mancode', '=', $mancode )
                ->first(); // 取出為物件

            $cdr_telcode = array($get_cdr_telcode->telcode => $get_cdr_telcode->userno_username);
            // dd($get_cdr_telcode, $cdr_telcode);
        }

        $inputStart = $request->input('date_start');
        $inputEnd = $request->input('date_end');
        $inputItnbr = $request->input('itnbr');
        $inputTelcode = $request->input('telcode');

        // POST | if 有設定條件並按下 "查詢" 鈕
        if( $inputStart && $inputEnd && $inputItnbr && $inputTelcode ){
            // dd($inputStart, $inputEnd, $inputItnbr, $inputTelcode); 
        
            $sql = "SELECT CONCAT(cdrcus.address1_utf8, cdrcus.address2_utf8) AS addr, "
                ."A.totamts, cdrcus.cusno, cdrcus.cusna_utf8, A.cmp_shpno, A.cmp_date, "
                ."cdrcus.tel1, cdrcus.tel3, return_hospname(cdrcus.cuycode) as cuycode "
                ."FROM cdrcus INNER JOIN ( "
                ."( SELECT cdrhad.totamts, cdrhad.shpno AS cmp_shpno, cdrhad.cusno, MAX(cdrhad.shpdate) AS cmp_date "
                ."FROM cdrhad INNER JOIN cdrdta ON cdrhad.shpno = cdrdta.shpno "
                ."WHERE (cdrhad.shpdate BETWEEN '$inputStart' AND '$inputEnd') AND cdrhad.telcode = '$inputTelcode' AND cdrhad.houtsta = 'Y' "
                ."AND (cdrdta.itnbr = '$inputItnbr' OR cdrdta.itnbr IN ( SELECT itnbrf FROM invbomd WHERE itnbr = '$inputItnbr' )) "
                ."GROUP BY cdrhad.cusno ) as A ) "
                // ."GROUP BY cdrhad.totamts, cdrhad.shpno, cdrhad.cusno ) as A ) "
                ."ON cdrcus.cusno = A.cusno "
                ."ORDER BY cdrcus.cuycode DESC ";
                
            $customer = DB::select(DB::raw($sql));
            
            // dd($customer); 

            foreach ($customer as $key => $row) {
                $shpno = DB::select('select MAX(shpno) as shpno from cdrhad where cusno = ? and shpdate = ? ', [$row->cusno, $row->cmp_date]);
                $customer[$key]->shpno = $shpno[0]->shpno;
            }

            // dd($customer); 
            return view('customer_itnbr')
                ->with('customer', $customer)
                ->with('psrs', $cdr_telcode)
                ->with('inputItnbr', $inputItnbr) // 帶出上一次 POST 查詢的 value 值: 'SBP2013'
                ->with('selected_psr', $inputTelcode) // 帶出 select 框的 option value 值: '0979635625'
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));

        } else {  // GET | 純觀看內容、沒有按下 "查詢"
            $inputStart = Carbon::now()->addDays(-31)->toDateString(); // (一個月前)
            $inputEnd = Carbon::now()->toDateString(); // 今日
            // dd( Carbon::now()->addDays(-31)->toDateString() ); // 2021-10-15 (一個月前)
            // dd( Carbon::now()->toDateString() ); // 2021-11-15

            return view('customer_itnbr')
                ->with('psrs', $cdr_telcode)
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));
        }
    }

    public function itnbr2( $yymm = null, Request $request )
    {  
        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = $ldepno.'%';
        $mancode = Session::get('mancode');
        $psr_type = DB::table('cdr_telcode')->where('mancode',$mancode )->first(); // 檢查是否為主管
        // dd( Auth::user(), $ldepno, $mancode, $psr_type );

        // if "不是" 業務
        if ( empty($psr_type) ){
            $get_cdr_telcode = DB::table('cdr_telcode')
                ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                ->where('cdr_telcode.pdepno', 'like', $ldepno)
                ->where('secuser.enabled', '=', 'Y')
                ->whereIn('cdr_telcode.pdepno', ['MNA','MNB','MNC','MND','MNE', 'MSA','MSB','MSC','MSD','MSE', 'MCA','MCB','MCC','MCD','MCE','TM'])
                ->orderby('cdr_telcode.pdepno', 'asc')
                ->orderby('userno_username', 'asc')
                ->orderby('telcode', 'asc')
                ->get();  // 取出為陣列包物件
          
            $cdr_telcode = array();
            foreach($get_cdr_telcode as $item){
                $cdr_telcode[$item->telcode] = $item->userno_username;
            }
           
            // dd($get_cdr_telcode, $cdr_telcode);
        } elseif( $psr_type->type <> 'PSR' ) { // 若為業務主管
            $get_cdr_telcode = DB::table('cdr_telcode')
                    ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                    ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                    ->where('cdr_telcode.pdepno', 'like', $ldepno)
                    ->where('secuser.enabled', '=', 'Y')
                    ->orderby('cdr_telcode.pdepno', 'asc')
                    ->orderby('userno', 'asc')
                    ->get();  // 取出為陣列包物件
            $cdr_telcode = array();
            foreach($get_cdr_telcode as $item){
                $cdr_telcode[$item->telcode] = $item->userno_username;
            }      
            // dd($get_cdr_telcode, $cdr_telcode);
        } else { // 若為業務組員 ( $psr_type->type = 'PSR' )
            $get_cdr_telcode = DB::table('cdr_telcode')
                ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                ->where( 'mancode', '=', $mancode )
                ->first(); // 取出為物件

            $cdr_telcode = array($get_cdr_telcode->telcode => $get_cdr_telcode->userno_username);
            // dd($get_cdr_telcode, $cdr_telcode);
        }

        $inputStart = $request->input('date_start');
        $inputEnd = $request->input('date_end');
        $inputItnbr = $request->input('itnbr');
        $inputItnbr2 = $request->input('itnbr2');
        $inputTelcode = $request->input('telcode');

        // POST | if 有設定條件並按下 "查詢" 鈕
        if( $inputStart && $inputEnd && $inputItnbr && $inputItnbr2 && $inputTelcode ){
            // dd($inputStart, $inputEnd, $inputItnbr, $inputItnbr2, $inputTelcode); 
        
            $sql = "SELECT CONCAT(cdrcus.address1_utf8, cdrcus.address2_utf8) AS addr, "
            ."A.totamts, cdrcus.cusno, cdrcus.cusna_utf8, A.cmp_shpno, A.cmp_date, "
            ."cdrcus.tel1, cdrcus.tel3, return_hospname(cdrcus.cuycode) as cuycode "
            ."FROM cdrcus "
            ."INNER JOIN ( "
            // *******
            ."SELECT * , MAX(B.cmp_date) FROM (SELECT totamts, cdrhad.shpno AS cmp_shpno, cdrhad.cusno, cdrhad.shpdate AS cmp_date "
            ."FROM cdrhad INNER JOIN cdrdta ON cdrhad.shpno = cdrdta.shpno "
            ."WHERE (cdrhad.shpdate BETWEEN '$inputStart' AND '$inputEnd') AND cdrhad.telcode = '$inputTelcode' AND cdrhad.houtsta = 'Y' "
            ."AND (cdrdta.itnbr = '$inputItnbr' OR cdrdta.itnbr IN ( SELECT itnbrf FROM invbomd WHERE itnbr = '$inputItnbr' ) )) AS B GROUP BY B.cusno ) as A "
            ."ON cdrcus.cusno = A.cusno "
            // *******
            ."INNER JOIN ( "
            ."SELECT * , MAX(D.cmp_date) FROM (SELECT totamts, cdrhad.shpno AS cmp_shpno, cdrhad.cusno, cdrhad.shpdate AS cmp_date "
            ."FROM cdrhad INNER JOIN cdrdta ON cdrhad.shpno = cdrdta.shpno "
            ."WHERE (cdrhad.shpdate BETWEEN '$inputStart' AND '$inputEnd') AND cdrhad.telcode = '$inputTelcode' AND cdrhad.houtsta = 'Y' "
            ."AND (cdrdta.itnbr = '$inputItnbr2' OR cdrdta.itnbr IN ( SELECT itnbrf FROM invbomd WHERE itnbr = '$inputItnbr2' ) )) AS D GROUP BY D.cusno ) as C "
            ."ON cdrcus.cusno = C.cusno "
            ."ORDER BY cdrcus.cuycode DESC";
                
            $customer = DB::select(DB::raw($sql));

            foreach ($customer as $key => $row) {
                $shpno = DB::select('select MAX(shpno) as shpno from cdrhad where cusno = ? and shpdate = ? ', [$row->cusno, $row->cmp_date]);
                $customer[$key]->shpno = $shpno[0]->shpno;
            }

            // dd($customer); 
            return view('customer_itnbr2')
                ->with('customer', $customer)
                ->with('psrs', $cdr_telcode)
                ->with('inputItnbr', $inputItnbr) // 帶出上一次 POST 查詢的 value 值: 'SBP2013'
                ->with('inputItnbr2', $inputItnbr2) // 帶出上一次 POST 查詢的 value 值: 'SBP2013'
                ->with('selected_psr', $inputTelcode) // 帶出 select 框的 option value 值: '0979635625'
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));

        } else {  // GET | 純觀看內容、沒有按下 "查詢"
            $inputStart = Carbon::now()->addDays(-31)->toDateString(); // (一個月前)
            $inputEnd = Carbon::now()->toDateString(); // 今日
            // dd( Carbon::now()->addDays(-31)->toDateString() ); // 2021-10-15 (一個月前)
            // dd( Carbon::now()->toDateString() ); // 2021-11-15

            return view('customer_itnbr2')
                ->with('psrs', $cdr_telcode)
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));
        }
    }

    //客戶查詢 - BBC
    public function itnbr3( $yymm = null, Request $request )
    {  
        $ldepno = (Auth::user()['ldepno'] == null) ? Auth::user()['pdepno'] : Auth::user()['ldepno'];
        $ldepno == 'A' ? $ldepno = '%' : $ldepno = $ldepno.'%';
        $mancode = Session::get('mancode');
        $psr_type = DB::table('cdr_telcode')->where('mancode',$mancode )->first(); // 檢查是否為主管
        // dd( Auth::user(), $ldepno, $mancode, $psr_type );

        // if "不是" 業務
        if ( empty($psr_type) ){
            $get_cdr_telcode = DB::table('cdr_telcode')
                ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                ->where('cdr_telcode.pdepno', 'like', $ldepno)
                ->where('secuser.enabled', '=', 'Y')
                ->whereIn('cdr_telcode.pdepno', ['MNA','MNB','MNC','MND','MNE', 'MSA','MSB','MSC','MSD','MSE', 'MCA','MCB','MCC','MCD','MCE','TM'])
                ->orderby('cdr_telcode.pdepno', 'asc')
                ->orderby('userno_username', 'asc')
                ->orderby('telcode', 'asc')
                ->get();  // 取出為陣列包物件
          
            $cdr_telcode = array();
            foreach($get_cdr_telcode as $item){
                $cdr_telcode[$item->telcode] = $item->userno_username;
            }
           
            // dd($get_cdr_telcode, $cdr_telcode);
        } elseif( $psr_type->type <> 'PSR' ) { // 若為業務主管
            $get_cdr_telcode = DB::table('cdr_telcode')
                    ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                    ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                    ->where('cdr_telcode.pdepno', 'like', $ldepno)
                    ->orderby('cdr_telcode.pdepno', 'asc')
                    ->orderby('userno', 'asc')
                    ->get();  // 取出為陣列包物件
            $cdr_telcode = array();
            foreach($get_cdr_telcode as $item){
                $cdr_telcode[$item->telcode] = $item->userno_username;
            }      
            // dd($get_cdr_telcode, $cdr_telcode);
        } else { // 若為業務組員 ( $psr_type->type = 'PSR' )
            $get_cdr_telcode = DB::table('cdr_telcode')
                ->join('secuser', 'cdr_telcode.mancode', '=', 'secuser.userno')
                ->select(DB::raw("telcode , CONCAT( cdr_telcode.pdepno , '[' , telcode ,']', username_utf8) AS userno_username"))
                ->where( 'mancode', '=', $mancode )
                ->first(); // 取出為物件

            $cdr_telcode = array($get_cdr_telcode->telcode => $get_cdr_telcode->userno_username);
            // dd($get_cdr_telcode, $cdr_telcode);
        }

        $inputStart = $request->input('date_start');
        $inputEnd = $request->input('date_end');
        $inputTelcode = $request->input('telcode');

        // POST | if 有設定條件並按下 "查詢" 鈕
        if( $inputStart && $inputEnd && $inputTelcode ){
            // dd($inputStart, $inputEnd, $inputItnbr, $inputTelcode); 
        
            $sql = "SELECT CONCAT(cdrcus.address1_utf8, cdrcus.address2_utf8) AS addr, "
                ."A.totamts, cdrcus.cusno, cdrcus.cusna_utf8, A.cmp_shpno, A.cmp_date, "
                ."cdrcus.tel1, cdrcus.tel3, return_hospname(cdrcus.cuycode) as cuycode "
                ."FROM cdrcus "
                ."INNER JOIN ( "
                ."SELECT * , MAX(B.cmp_date) FROM (SELECT totamts, cdrhad.shpno AS cmp_shpno, cdrhad.cusno, cdrhad.shpdate AS cmp_date "
                ."FROM cdrhad "
                ."WHERE (cdrhad.shpdate BETWEEN '$inputStart' AND '$inputEnd') AND cdrhad.telcode = '$inputTelcode' AND cdrhad.houtsta = 'Y' AND contractno like '%BBC%' "
                ." ) AS B GROUP BY B.cusno ) as A "
                ."ON cdrcus.cusno = A.cusno "
                ."ORDER BY cdrcus.cuycode DESC";
                
            $customer = DB::select(DB::raw($sql));

            foreach ($customer as $key => $row) {
                $shpno = DB::select('select MAX(shpno) as shpno from cdrhad where cusno = ? and shpdate = ? ', [$row->cusno, $row->cmp_date]);
                $customer[$key]->shpno = $shpno[0]->shpno;
            }

            // dd($customer); 
            return view('customer_itnbr3')
                ->with('customer', $customer)
                ->with('psrs', $cdr_telcode)
                ->with('selected_psr', $inputTelcode) // 帶出 select 框的 option value 值: '0979635625'
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));

        } else {  // GET | 純觀看內容、沒有按下 "查詢"
            $inputStart = Carbon::now()->addDays(-31)->toDateString(); // (一個月前)
            $inputEnd = Carbon::now()->toDateString(); // 今日
            // dd( Carbon::now()->addDays(-31)->toDateString() ); // 2021-10-15 (一個月前)
            // dd( Carbon::now()->toDateString() ); // 2021-11-15

            return view('customer_itnbr3')
                ->with('psrs', $cdr_telcode)
                ->with('shpdate', array('inputStart'=>$inputStart, 'inputEnd'=>$inputEnd));
        }
    }
}
