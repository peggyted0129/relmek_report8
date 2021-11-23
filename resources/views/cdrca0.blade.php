@extends('layout')

@section('content')
<div class="container mt-7" style="margin-bottom: 250px">
  <div class="row align-items-center font-weight-bolder mb-5">
    <div class="col-4 col-md-3">
      <a href="{{url('/cdrca0/'.$parms['yymm_last'])}}" type="button" class="btn btn-info px-sm-10 px-5">上月</a>
    </div>
    <div class="col-md-3 text-center d-none d-md-block">連鎖客戶業績統計</div>
    <div class="col-4 col-md-3 text-center">{{ $parms['yymm']}}</div>
    <div class="col-4 col-md-3 d-flex">
      <a href="{{url('/cdrca0/'.$parms['yymm_next'])}}" type="button" class="btn btn-info px-sm-10 px-5 ml-auto">下月</a>
    </div>
  </div>

  <div class="table-responsive" style="font-size:14px">
    <table class="table table-hover border">
      <thead>
        <tr class="bg-topic">
          <th scope="col" class="text-center">連鎖代號</th>
          <th scope="col" class="text-center">連鎖客戶</th>
          <th scope="col" class="text-center">累計退貨</th>
          <th scope="col" class="text-center">累計業績</th>
          <th scope="col" class="text-center">業績目標</th>
          <th scope="col" class="text-center">達成率</th>
          <th scope="col" class="text-center">去年同期</th>
          <th scope="col" class="text-center">成長率</th>
        </tr>
      </thead>
      <tbody>
        @foreach( $cdrca0 as $item )
          @if( $item->agentfacno == 'SUM' )
            <tr class="bg-theme">
          @else
            <tr class="bg-lgreen">
          @endif
            <td class="text-center"> {{ $item->agentfacno }} </td>
            <td class="text-center"> {{ $item->cusna }} </td>
            <td class="text-right"> {{ $item->bakamt }} </td>
            <td class="text-right"> {{ $item->sales }} </td>
            <td class="text-right"> {{ $item->target }} </td>
            <td class="text-right"> {{ $item->rate }} </td>
            <td class="text-right"> {{ $item->lastsales }} </td>
            <td class="text-right"> {{ $item->rate2 }} </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection