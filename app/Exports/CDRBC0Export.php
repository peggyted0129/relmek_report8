<?php

namespace App\Exports;

use App\Models\Cdrsachv2;
use Illuminate\Support\Facades\DB;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class CDRBC0Export implements FromCollection, WithHeadings
{
    public function headings():array
    {
        return [
            '部門',
            '前日出貨',
            'Sebamed',
            'ODO',
            'Salcura',
            'Bullet&Bone',
            'Mira teeth',
            'Phyto',
            'Noreva',
            '累計退貨',
            '累計業績',
            '業績目標',
            '達成率'
        ];
    }

    public function collection()
    {
        $yymm = date('Ym'); // 取得當日的年月
        $yymm_last = date_add(date_create($yymm.'01'), date_interval_create_from_date_string("-1 month"))->format('Ym') ; // 取得 "上個月"
        $cdrsachv_depno = Cdrsachv2::exportsalesbydepno($yymm_last); // 取得 "上個月" 的業績
        // dd($cdrsachv_depno);

        return collect($cdrsachv_depno);
    }
}
