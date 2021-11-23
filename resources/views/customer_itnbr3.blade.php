@extends('layout')

@section('content')
<div class="container-lg mt-7">
  <form method="POST" action="{{ route('customer.itnbr3') }}">
    @csrf
    <div class="form-row">
      <div class="form-group col-6 col-md-3 col-lg-2">
        <label for="date_start">開始時間 : </label>
        <input type="text" class="form-control" id="date_start" name="date_start" value={{ $shpdate['inputStart'] }}>
      </div>
      <div class="form-group col-6 col-md-3 col-lg-2">
        <label for="date_end">結束時間 : </label>
        <input type="text" class="form-control" id="date_end" name="date_end" value={{ $shpdate['inputEnd'] }}>
      </div>
      <div class="form-group col-12 col-sm-6 col-md-4 col-xl-3">
        <label for="telcode">業務 : </label>
        <select id="telcode" class="form-control js_InputTelcode" name="telcode">
          @foreach($psrs as $key => $psr)
            @if( !empty($selected_psr) && ($key == $selected_psr) ) {{-- POST 行為後設定為預設值 --}}
              <option value={{ $key }} selected> {{ $psr }} </option>
            @else
              <option value={{ $key }}> {{ $psr }} </option>
            @endif
          @endforeach
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary w-25 js_customer_itnbr_btn">查詢</button>
  </form>
</div>
<div class="container-fluid mt-6 mb-19" style="font-size:14px">
  <p class="mb-3 h6 font-weight-bolder"> 媽媽寶寶俱樂部 </p>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr class="bg-topic text-center p-2">
          <th scope="col" class="text-center">單據編號</th>
          <th scope="col" class="text-center">代號</th>
          <th scope="col" class="text-center">客戶名稱</th>
          <th scope="col" class="text-center">機關</th>
          <th scope="col" class="text-center">聯絡電話</th>
          <th scope="col" class="text-center">手機</th>
          <th scope="col" class="text-center">出貨金額</th>
          <th scope="col" class="text-center">地址</th>
        </tr>
      </thead>
      <tbody>
        @if( !empty($customer) )  
          @foreach($customer as $item)
            <tr>
              <td class="text-center p-1">
                <button class="btn btn-light border border-secondary js_itnbr" data-shpno={{ $item->shpno }}>{{$item->shpno}}</button>
              </td>
              <td class="text-center p-1">{{$item->cusno}}</td>
              <td class="text-center p-1">{{$item->cusna_utf8}}</td>
              <td class="text-center p-1">{{$item->cuycode}}</td>
              <td class="text-center p-1">{{$item->tel1}}</td>
              <td class="text-center p-1">{{$item->tel3}}</td>
              <td class="text-right p-1">{{number_format($item->totamts, 0)}}</td>
              <td class="text-center p-1">{{$item->addr}}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td></td>
          </tr>
        @endif
      </tbody>
    </table>
  </div>
</div>

{{-- // ***** popup 內容 ***** // --}}
<div id="popup" style="display: none;">
  <span class="popup-button b-close"><span>X</span></span>
  <h2 class="popup-logo font-weight-bolder text-center js-itnbr-popup-title mb-3 mt-3">
    出貨單明細 
  </h2>
  <div class="table-responsive">
    <table class="table border">
      <thead>
        <tr class="head-tr bg-light">
          <th scope="col" class="text-left">品號</th>
          <th scope="col" class="text-left">品名</th>
          <th scope="col" class="text-right">數量</th>
          <th scope="col" class="text-right">金額</th>
        </tr>
      </thead>
      <tbody class="js-itnbr-popup-content">
        {{-- <td> 使用 js 插入內容 </td> --}}
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('reportjs')
<script>
  $("#date_start").datetimepicker({
    timepicker:false,
    format:'Y-m-d'
  });
  $("#date_end").datetimepicker({
    timepicker:false,
    format:'Y-m-d'
  });

  let js_itnbr = document.querySelectorAll(".js_itnbr");
  let itnbrLogo = document.querySelector('.js-itnbr-popup-title');
  let itnbrTable = document.querySelector('.js-itnbr-popup-content');

  js_itnbr.forEach((item, key) => {
    js_itnbr[key].addEventListener("click", function(e){
      // console.log(e.target.dataset.shpno);
      let getShpno = e.target.dataset.shpno;
      let currentUrl = getCurrentUrl();

      $.ajax({
        url: `${ currentUrl }/cdrba0/${ getShpno }`,
        type: 'get',
        cache: false,
        dataType: 'json',
        success: function(data) {
          console.log(data.data.cdrdta);
          let getCdrdta = data.data.cdrdta;
          itnbrLogo.textContent = `出貨單明細 ${ getShpno }`;

          let str = '';
          getCdrdta.forEach(item => {
            str += `<tr class="head-tr bg-topic">
                      <td class="text-left">${ item.itnbr }</td>
                      <td class="text-left">${ item.itdsc_utf8 } ${ item.spdsc_utf8 }</td>
                      <td class="text-right">${ item.shpqy1 }</td>
                      <td class="text-right">${ item.shpamts }</td>
                    </tr>`
          })
          // console.log(str);
          itnbrTable.innerHTML = str;
          
          $('#popup').bPopup();
        }
      })
    })
  })
</script>
@endsection