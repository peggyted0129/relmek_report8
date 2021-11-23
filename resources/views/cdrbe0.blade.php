@extends('layout')

@section('content')
<div class="container mt-7" style="margin-bottom: 250px">
  <div class="row align-items-center font-weight-bolder mb-5">
    <div class="col-4 col-md-3">
      <a href="{{url('/cdrbe0/'.$parms['yymm_last'])}}" type="button" class="btn btn-info px-sm-10 px-5">上月</a>
    </div>
    <div class="col-md-3 text-center d-none d-md-block">業務新客戶統計</div>
    <div class="col-4 col-md-3 text-center">{{ $parms['yymm']}}</div>
    <div class="col-4 col-md-3 d-flex">
      <a href="{{url('/cdrbe0/'.$parms['yymm_next'])}}" type="button" class="btn btn-info px-sm-10 px-5 ml-auto">下月</a>
    </div>
  </div>

  <div class="table-responsive" style="font-size:14px">
    <table class="table">
      <thead>
        <tr class="bg-topic">
          <th scope="col">部門</th>
          <th scope="col">業務</th>
          <th scope="col">當月新增客戶數</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cdrbe0 as $item)
          @if( null == $item->mandsc )
            <tr class="bg-theme">
              <td>{{$item->pdepno}}</td>
              @if( null == $item->pdepno && null == $item->mandsc )
                <td class="text-left">總計</td>
              @else
                <td class="text-left">小計</td>
              @endif
          @else
            <tr class="bg-lgreen">
              <td class="text-left">{{$item->pdepno}}</td>
              <td class="text-left">{{$item->mandsc}}</td>
          @endif
              <td class="text-right">{{$item->qty}}</td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection