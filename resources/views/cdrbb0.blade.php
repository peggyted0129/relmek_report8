@extends('layout')

@section('content')
{{-- <div class="container mt-7" style="margin-bottom: 250px"> --}}
<div class="container my-7">
  <div class="row align-items-center mb-5 font-weight-bolder">
    <div class="col-4 col-md-3">
      <a href="{{url('/cdrbb0/'.$parms['yymm_last'])}}" type="button" class="btn btn-info px-sm-10 px-5">上月</a>
    </div>
    <div class="col-md-3 d-none d-md-block text-center">業務單品排行</div>
    <div class="col-4 col-md-3 text-center">{{ $parms['yymm']}}</div>
    <div class="col-4 col-md-3 d-flex">
      <a href="{{url('/cdrbb0/'.$parms['yymm_next'])}}" type="button" class="btn btn-info px-sm-10 px-5 ml-auto">下月</a>
    </div>
  </div>
</div>
<div class="cdrbb0-container mx-auto" style="font-size:14px; margin-bottom: 250px">
  <div class="table-responsive">
    <table class="table table-hover cdrbb0-table">
      <thead>
        <tr class="head-tr bg-topic">
          <td scope="col">單品</td>
          <td scope="col">品名</td>
          <td scope="col">PSR出貨數量</td>
          <td scope="col">PSR退貨數量</td>
          <td scope="col">出貨數量</td>
          <td scope="col">退貨數量</td>
          <td scope="col">銷量</td>
          <td scope="col">PSR出貨金額</td>
          <td scope="col">PSR退貨金額</td>
          <td scope="col">出貨金額</td>
          <td scope="col">退貨金額</td>
          <td scope="col">銷金</td>
        </tr>
      </thead>
      @foreach($cdrbb0 as $item)
        <tr>
          <td>
            <button type="button" class="btn btn-light px-3 border border-secondary js-cdrbb0" 
              data-date="{{ $parms['yymm']}}" data-itnbr="{{$item->itnbr}}" style="font-size:14px">{{$item->itnbr}}</button>
          </td>
          <td class="text-left">{{ $item->itdsc_utf8 }} {{ $item->spdsc_utf8 }}</td>
          <td class="text-right">{{ $item->psr_qty }}</td>
          <td class="text-right">{{ $item->psr_bakqty }}</td>
          <td class="text-right">{{ $item->qty }}</td>
          <td class="text-right">{{ $item->bakqty }}</td>
          <td class="text-right">{{ $item->qty + $item->bakqty }}</td>
          <td class="text-right">{{ $item->psr_shpamt }}</td>
          <td class="text-right">{{ $item->psr_bakamt }}</td>
          <td class="text-right">{{ $item->shpamt }}</td>
          <td class="text-right">{{ $item->bakamt }}</td>
          <td class="text-right">{{ $item->shpamt + $item->bakamt }}</td>
        </tr>
      @endforeach
    </table>
  </div>
</div>



{{-- // ***** popup 內容 ***** // --}}
<div id="popup" style="display: none;">
  <span class="popup-button b-close"><span>X</span></span>
  <h2 class="popup-logo font-weight-bolder text-center js-cdrbb0-popup-title mb-3">
    {{-- 使用 js 插入標題 --}}
  </h2>
  <div class="table-responsive">
    <table class="table cdrbc0-table border">
      <thead>
        <tr class="head-tr bg-light">
          <th scope="col">業務</th>
          <th scope="col">出貨</th>
          <th scope="col">退貨</th>
          <th scope="col">出貨金額</th>
          <th scope="col">退貨金額</th>
        </tr>
      </thead>
      <tbody class="js-cdrbb0-popup-content">
        {{-- <td> 使用 js 插入內容 </td> --}}
      </tbody>
    </table>
  </div>
</div>

@endsection

@section('reportjs')
  <script>
    let el = document.querySelectorAll('.js-cdrbb0');
    let cdrbb0Logo = document.querySelector('.js-cdrbb0-popup-title');
    let cdrbb0Table = document.querySelector('.js-cdrbb0-popup-content');
    let date = '';
    let itnbr = '';
  
    el.forEach((item, key) => {
      el[key].addEventListener('click', function(e){
        date = e.target.dataset.date;
        itnbr = e.target.dataset.itnbr;
        console.log(date, itnbr);
        let currentUrl = getCurrentUrl();

        $.ajax({
          url: `${ currentUrl }/cdrbb0/${ date }/${ itnbr }`,
          type: 'get',
          cache: false,
          dataType: 'json',
          success: function(data) {
            let cdrb96_data = data.data.cdrb96_data
            console.log(cdrb96_data);

            cdrbb0Logo.textContent = `${ cdrb96_data[0].itnbr } ${ cdrb96_data[0].itdsc_utf8 } ${ cdrb96_data[0].spdsc_utf8 }`;

            let str = '';
            cdrb96_data.forEach(item => {
              str += `<tr class="head-tr bg-topic border">
                        <td class="text-center">${ item.userno } ${ item.username_utf8 }</td>
                        <td class="text-right">${ item.qty }</td>
                        <td class="text-right">${ item.bakqty }</td>
                        <td class="text-right">${ item.shpamt }</td>
                        <td class="text-right">${ item.bakamt }</td>
                      </tr>`
            })
            // console.log(str);
            cdrbb0Table.innerHTML = str;

            $('#popup').bPopup();
          }
        })
      })
    });
  </script>
@endsection