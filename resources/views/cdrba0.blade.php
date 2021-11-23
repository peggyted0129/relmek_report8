@extends('layout')

@section('content')
<div class="container mt-7">
  <form method="POST" action="{{ route('cdrba0') }}">
    @csrf
    <div class="form-row">
      <div class="form-group col-6 col-sm-3 col-md-3 col-lg-2">
        <label for="start">開始時間 : </label>
        <input type="text" class="form-control" id="start" name="start" value={{ $shpdate['inputStart'] }}>
      </div>
      <div class="form-group col-6 col-sm-3 col-md-3 col-lg-2">
        <label for="end">結束時間 : </label>
        <input type="text" class="form-control" id="end" name="end" value={{ $shpdate['inputEnd'] }}>
      </div>
      <div class="form-group col-sm-6 col-md-4 col-lg-3">
        <label for="inputPsr">業務 : </label>
        <select id="inputPsr" class="form-control jsInputPsr" name="inputPsr">
          @foreach($psrs as $key => $psr)
            @if( !empty($cdrhad) && ($key == $selected_psr) ) {{-- POST 行為後設定為預設值 --}}
              <option value={{ $key }} selected> {{ $psr }} </option>
            @else
              <option value={{ $key }}> {{ $psr }} </option>
            @endif
          @endforeach
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary w-25">查詢</button>
  </form>
</div>
<div class="container-fluid mt-6 mb-19" style="font-size:14px">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr class="bg-topic text-center p-2">
          <th scope="col">單據編號</th>
          <th scope="col">代號</th>
          <th scope="col">客戶名稱</th>
          <th scope="col">機關</th>
          <th scope="col">聯絡電話</th>
          <th scope="col">送貨別</th>
          <th scope="col">出貨金額</th>
          <th scope="col">付款別</th>
          <th scope="col">收款金額</th>
          <th scope="col">出貨地址</th>
        </tr>
      </thead>
      <tbody>
        @if( !empty($total) )
          <tr class="bg-lgreen">
            <td>合計: </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-right">{{ $total['totamts'] }}</td>
            <td></td>
            <td class="text-right">{{ $total['tnfamt'] }}</td>
            <td class="text-left"></td>
          </tr>
        @endif
       
        @if( !empty($cdrhad) )
          @foreach($cdrhad as $item)
            <tr class="border-bottom">
              <td class="text-left">
                <button type="button" class="btn btn-light border border-secondary js_cdrba0" data-shpno={{ $item->shpno }}> {{ $item->shpno }} </button>
              </td>
              <td class="text-left"> {{ $item->cusno }} </td>
              <td class="text-left"> {{ $item->cusna }} </td>
              <td class="text-left"> {{ $item->cuycode }} </td>
              <td class="text-left"> {{ $item->tel }} </td>
              <td class="text-left"> {{ $item->sndcode }} </td>
              <td class="text-right"> {{ $item->totamts }} </td>
              <td class="text-left"> {{ $item->paycode }} </td>
              <td class="text-right"> {{ $item->tnfamt }} </td>
              <td class="text-left"> {{ $item->address }} </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
</div>

{{-- // ***** popup 內容 ***** // --}}
<div id="popup" style="display: none;">
  <span class="popup-button b-close"><span>X</span></span>
  <h2 class="popup-logo font-weight-bolder text-center js-cdrba0-popup-title mb-3 mt-3">
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
      <tbody class="js-cdrba0-popup-content">
        {{-- <td> 使用 js 插入內容 </td> --}}
      </tbody>
    </table>
  </div>
</div>

@endsection

@section('reportjs')
<script>
  $("#start").datetimepicker({
    timepicker:false,
    format:'Y-m-d'
  });
  $("#end").datetimepicker({
    timepicker:false,
    format:'Y-m-d'
  });

  let js_cdrba0 = document.querySelectorAll(".js_cdrba0");
  let cdrba0Logo = document.querySelector('.js-cdrba0-popup-title');
  let cdrba0Table = document.querySelector('.js-cdrba0-popup-content');

  js_cdrba0.forEach((item, key) => {
    js_cdrba0[key].addEventListener("click", function(e){
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
          cdrba0Logo.textContent = `出貨單明細 ${ getShpno }`;

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
          cdrba0Table.innerHTML = str;
          
          $('#popup').bPopup();
        }
      })
    })
  })
  
</script>
@endsection