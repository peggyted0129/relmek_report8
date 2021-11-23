@extends('layout')

@section('content')
  <div class="container my-7">
    <div class="row align-items-center font-weight-bolder">
      <div class="col-4 col-sm-5 col-lg-3 col-xl-2">
        <a href="{{url('/cdrbc0/'.$parms['yymm_last'])}}" type="button" class="btn btn-info px-sm-10 px-5">上月</a>
      </div>
      <div class="col-lg-3 col-xl-3 text-center d-lg-block d-none">業績達成分析 CDRBC0</div>
      <div class="col-4 col-sm-2 col-lg-3 col-xl-3 text-center">{{ $parms['yymm']}}</div>
      <div class="col-xl-2 text-center d-xl-block d-none">{{ $parms['t_time']}}</div>
      <div class="col-4 col-sm-5 col-lg-3 col-xl-2 d-flex">
        <a href="{{url('/cdrbc0/'.$parms['yymm_next'])}}" type="button" class="btn btn-info px-sm-10 px-5 ml-auto">下月</a>
      </div>
    </div>
  </div>

  <div class="container-fluid mb-19" style="font-size:14px">
    <div class="table-responsive">
      <table class="table cdrbc0-table">
        <thead>
          <tr class="head-tr bg-topic">
            <th scope="col" width="5%">部門</th>
            <th scope="col">前日出貨</th>
            <th scope="col">Sebamed</th>
            <th scope="col">ODO</th>
            <th scope="col">Salcura</th>
            <th scope="col">Bullet&Bone</th>
            <th scope="col">Mira teeth</th>
            <th scope="col">Phyto</th>
            <th scope="col">Noreva</th>
            <th scope="col">累計退貨</th>
            <th scope="col">累計業績</th>
            <th scope="col">業績目標</th>
            <th scope="col">達成率</th>
          </tr>
        </thead>
        <tbody>
          @foreach( $cdrsachv_depno as $item )
            <tr class="bg-light border-top border-bottom">
              <td class="d-flex justify-content-center">
                <a class="btn btn-info px-10" data-toggle="collapse" href="#collapse-{{$item->pdepno}}" style="font-size:14px">{{$item->pdepno}}</a>
              </td>
              <td class="text-right">{{$item->SALES_DAY}}</td>
              <td class="text-right">{{$item->SALES_S}}</td>
              <td class="text-right">{{$item->SALES_O}}</td>
              <td class="text-right">{{$item->SALES_V}}</td>
              <td class="text-right">{{$item->SALES_B}}</td>
              <td class="text-right">{{$item->SALES_R}}</td>
              <td class="text-right">{{$item->SALES_H}}</td>
              <td class="text-right">{{$item->SALES_A}}</td>
              <td class="text-right">{{$item->SALES_BAK}}</td>
              <td class="text-right">{{$item->SALES}}</td>
              <td class="text-right">{{$item->TARGET}}</td>
              <td class="text-right">{{$item->PERC2}}</td>
            </tr>
            <tr>
              <td colspan="11" class="px-4">
                <div class="collapse" id="collapse-{{$item->pdepno}}">
                  <div class="table-responsive">
                    <table class="table">
                      <thead>
                        <tr colspan="11">
                          <td class="px-0">
                            <a class="btn btn-warning text-white px-4" data-toggle="collapse" href="#lastyear-{{$item->pdepno}}" style="font-size:14px">去年同期</a>
                          </td>
                        </tr>
                        <tr class="head-tr bg-topic">
                          <th scope="col">部門</th>
                          <th scope="col">前日出貨</th>
                          <th scope="col">Sebamed</th>
                          <th scope="col">ODO</th>
                          <th scope="col">Salcura</th>
                          <th scope="col">Bullet&Bone</th>
                          <th scope="col">Mira teeth</th>
                          <th scope="col">Phyto</th>
                          <th scope="col">Noreva</th>
                          <th scope="col">累計退貨</th>
                          <th scope="col">累計業績</th>
                          <th scope="col">業績目標</th>
                          <th scope="col">達成率</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach(  $cdrsachv_psr as $psr )
                          @if( $item->pdepno == $psr->fdepno )
                            @if( ($psr->yymm == $parms['yymm']) && (strpos($psr->mandsc,'sum') > 0) )
                              <tr class="bg-theme">
                                <td class="left">{{ $psr->mandsc }}</td>
                            @elseif( $psr->yymm == $parms['yymm'] )
                              <tr class="bg-streak">
                                <td class="left">{{ $psr->mandsc }}</td>
                            @else 
                              <tr class="bg-glass collapse" class="collapse" id="lastyear-{{$item->pdepno}}">
                                <td class="left">去年 {{ $psr->mancode }}</td>
                            @endif

                                <td class="text-right">{{ $psr->SALES_DAY }}</td>
                                <td class="text-right">{{ $psr->SALES_S }}</td>
                                <td class="text-right">{{ $psr->SALES_O }}</td>
                                <td class="text-right">{{ $psr->SALES_V }}</td>
                                <td class="text-right">{{ $psr->SALES_B }}</td>
                                <td class="text-right">{{ $psr->SALES_R }}</td>
                                <td class="text-right">{{ $psr->SALES_H }}</td>
                                <td class="text-right">{{ $psr->SALES_A }}</td>
                                <td class="text-right">{{ $psr->SALES_BAK }}</td>
                                <td class="text-right">{{ $psr->SALES }}</td>
                                @if( $psr->yymm == $parms['yymm'] )
                                  <td class="text-right">{{ $psr->TARGET }}</td>
                                  <td class="text-right">{{ $psr->PERC2 }}</td>
                                @else
                                  <td></td>
                                  <td></td>
                                @endif

                              </tr> 
                          @endif 
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </td>
            </tr>
            
          @endforeach
        </tbody>  
      </table>
    </div>
  </div>
@endsection