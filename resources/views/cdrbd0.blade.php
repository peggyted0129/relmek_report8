@extends('layout')

@section('content')
<div class="container mt-7" style="margin-bottom:230px">
  <div class="row align-items-center font-weight-bolder mb-5">
    <div class="col-4 col-md-3">
      <a href="{{url('/cdrbd0/'.$parms['yymm_last'])}}" type="button" class="btn btn-info px-sm-10 px-5">上月</a>
    </div>
    <div class="col-md-3 text-center d-none d-md-block">業務展示說明會統計</div>
    <div class="col-4 col-md-3 text-center">{{ $parms['yymm']}}</div>
    <div class="col-4 col-md-3 d-flex">
      <a href="{{url('/cdrbd0/'.$parms['yymm_next'])}}" type="button" class="btn btn-info px-sm-10 px-5 ml-auto">下月</a>
    </div>
  </div>
  <div class="table-responsive" style="font-size:14px">
    <table class="table">
      <thead>
        <tr class="bg-topic">
          <th class="text-center" scope="col">部門</th>
          <th class="text-center" scope="col">業務</th>
          <th class="text-center" scope="col">展示會場次</th>
          <th></th>
          <th class="text-center" scope="col">說明會場次</th>
        </tr>
      </thead>
      <tbody>
        @foreach($cdrbd0 as $item)
          @if( $item->mandsc==null )
            <tr class="bg-theme">
              <td class="text-center">{{ $item->pdepno }}</td>
              @if( null == $item->pdepno )
                <td class="text-center">總計</td>
              @else
                <td class="text-center">小計</td>
              @endif
              <td class="text-right">{{ $item->cd3 }}</td>
              <td class="text-center">{{ $item->mandsc }}</td>
              <td class="text-right">{{ $item->cd5 }}</td>
            </tr>
          @else
            <tr class="bg-lgreen">
              <td class="text-center">{{ $item->pdepno }}</td>
              <td class="text-center">
                <button type="button" class="btn btn-light btn-sm border border-secondary w-75 js_cdrbd0_had" data-yymm={{ $parms['yymm'] }} data-mandsc={{ $item->mandsc }}> {{ $item->mandsc }} </button>
              </td>
              <td class="text-right">{{ $item->cd3 }}</td>
              <td class="text-center">
                <button type="button" class="btn btn-light btn-sm border w-75 js_cdrbd0_eip border-secondary" data-yymm={{ $parms['yymm'] }} data-mandsc={{ $item->mandsc }}> {{ $item->mandsc }} </button>
              </td>
              <td class="text-right">{{ $item->cd5 }}</td>
            </tr>
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- // ***** popup_had 內容 ***** // --}}
<div id="popup" style="display: none;">
  <span class="popup-button b-close"><span>X</span></span>
  <h2 class="popup-logo font-weight-bolder text-left mb-3 mt-3 js_cdrbd0_title">
    {{-- 使用 js 插入內容 --}}
  </h2>
  <div class="table-responsive" style="font-size:14px">
    <table class="table border">
      <thead>
        <tr class="head-tr bg-light">
          <th scope="col" class="text-center">品號</th>
          <th scope="col" class="text-center">日期</th>
          <th scope="col" class="text-center">展示點 (新)</th>
          <th scope="col" class="text-center">展示點</th>
        </tr>
      </thead>
      <tbody class="js_cdrbd0_popup_table">
        {{-- <td> 使用 js 插入內容 </td> --}}
      </tbody>
    </table>
  </div>
</div>
@endsection

@section('reportjs')
<script>
  let js_cdrbd0_title = document.querySelector(".js_cdrbd0_title"); // popup 標題
  let js_cdrbd0_had = document.querySelectorAll(".js_cdrbd0_had"); // 展示會按鈕
  let js_cdrbd0_eip = document.querySelectorAll(".js_cdrbd0_eip"); // 說明會按鈕
  let js_cdrbd0_popup_table = document.querySelector(".js_cdrbd0_popup_table"); // popup 內容
  
  js_cdrbd0_had.forEach((item, key) => { // 展示會按鈕監聽
    js_cdrbd0_had[key].addEventListener("click", function(e){
      console.log(e.target.dataset.yymm, e.target.dataset.mandsc);
      let yymm = e.target.dataset.yymm;
      let mandsc = e.target.dataset.mandsc; 
      let currentUrl = getCurrentUrl();

      $.ajax({
        url: `${ currentUrl }/cdrbd0_cdrhad/${ yymm }/${ mandsc }`,
        type: 'get',
        cache: false,
        dataType: 'json',
        success: function(data) {
          console.log(data);
          // console.log(data.data.cdrhmas_sap);

          if(data.status == 0){
            alert(); // alert 錯誤訊息
            // js_cdrbd0_title.textContent = '展示會場次明細';
            // js_cdrbd0_popup_table.innerHTML = '';
            // $('#popup').bPopup();
          } else {
            let getData = data.data.cdrhmas_sap;
            let str = '';
            getData.forEach(item => {
              str += `<tr class="head-tr bg-topic">
                        <td class="text-center">${ item.cdrno }</td>
                        <td class="text-center">${ item.act_date }</td>
                        <td class="text-left">${ item.firm_name_utf8 }</td>
                        <td class="text-left">${ item.hospname_utf8 }</td>
                      </tr>`
            })

            js_cdrbd0_title.textContent = '展示會場次明細';
            js_cdrbd0_popup_table.innerHTML = str;
            $('#popup').bPopup();
          }
        }
      })
    })
  })

  js_cdrbd0_eip.forEach((item, key) => { // 說明會按鈕監聽
    js_cdrbd0_eip[key].addEventListener("click", function(e){
      // console.log(e.target.dataset.yymm, e.target.dataset.mandsc);
      
      let yymm = e.target.dataset.yymm;
      let mandsc = e.target.dataset.mandsc; 
      let currentUrl = getCurrentUrl();

      $.ajax({
        url: `${ currentUrl }/cdrbd0_eip/${ yymm }/${ mandsc }`,
        type: 'get',
        cache: false,
        dataType: 'json',
        success: function(data) {
          if(data.status == 0){
            console.log(data);
            alert(); // alert 錯誤訊息
          } else {
            console.log(data.data.eip200);
            let getData = data.data.eip200;
            let str = '';
            getData.forEach(item => {
              str += `<tr class="head-tr bg-topic">
                        <td class="text-center">${ item.eip200_f00 }</td>
                        <td class="text-center">${ item.eip200_f02 }</td>
                        <td class="text-left">${ item.eip200_f07 }${ item.hospname_utf8 }</td>
                        <td class="text-left">${ item.eip200_f09 }</td>
                      </tr>`
            })
            js_cdrbd0_title.textContent = '說明會場次明細';
            js_cdrbd0_popup_table.innerHTML = str;
            $('#popup').bPopup();
          }
        }
      })
    })
  })

</script>
@endsection
