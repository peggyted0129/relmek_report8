<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="_token" content="{!! csrf_token() !!}">
  <title>@yield('title')</title>
  <link rel="stylesheet" type="text/css" href="{{ asset(mix('/css/app.css')) }}">
  <script src="{{ asset(mix('/js/app.js')) }}"></script> {{-- 引入 jquery --}}
  <script src="{{ asset(mix('/js/jquery.bpopup.min.js')) }}"></script>
  <script src="{{ asset(mix('/js/jquery.datetimepicker.full.min.js')) }}"></script>
  <script src="{{ asset(mix('/js/moment.min.js')) }}"></script>
  <script src="{{ asset(mix('/js/sweetalert2.js')) }}"></script>
</head>
<body>
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-45273841-1', 'auto');
    ga('send', 'pageview');
  </script>
  <nav class="navbar navbar-expand-lg navbar-light bg-light py-0">
    <a class="navbar-brand" href={{ url('/cdrbc0') }}>Relmek</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav w-100">
        <li class="nav-item">
          <a class="nav-link px-5 py-5" href={{ url('../../about') }}>About</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle px-5 py-5" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Report
          </a>
          <!-- 手機版 RWD -->
          <div class="dropdown-menu d-lg-none" aria-labelledby="navbarDropdownMenuLink">
            <a class="dropdown-item" href={{ url('/cdrbc0') }}>業績</a>
            <a class="dropdown-item" href={{ url('/cdrbb0') }}>單品</a>
            <a class="dropdown-item" href={{ url('/cdrba0') }}>訂單</a>
            <a class="dropdown-item" href={{ url('/cdrbd0') }}>展示</a>
            <a class="dropdown-item" href={{ url('/cdrbe0') }}>新客戶</a>
            <a class="dropdown-item" href={{ url('/cdrca0') }}>OTC連鎖客戶業績</a>
            <a class="dropdown-item" href={{ url('/customer/itnbr') }}>客戶出貨查詢</a>
            <a class="dropdown-item" href={{ url('/customer/itnbr2') }}>客戶出貨查詢2</a>
            <a class="dropdown-item" href={{ url('/customer/itnbr3') }}>媽媽寶寶俱樂部</a>
          </div>
          <!-- 電腦版 RWD-->
          <div class="navbar-menu d-none d-lg-block">
            <a class="navbar-menu-item px-5 py-2" href={{ url('/cdrbc0') }}>業績</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/cdrbb0') }}>單品</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/cdrba0') }}>訂單</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/cdrbd0') }}>展示</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/cdrbe0') }}>新客戶</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/cdrca0') }}>OTC連鎖客戶業績</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/customer/itnbr') }}>客戶出貨查詢</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/customer/itnbr2') }}>客戶出貨查詢2</a>
            <a class="navbar-menu-item px-5 py-2" href={{ url('/customer/itnbr3') }}>媽媽寶寶俱樂部</a>
          </div>
        </li>
        </li>
        <li class="nav-item">
          <a class="nav-link px-5 py-5" href={{ url('../../forum') }}>討論區</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-5 py-5" href={{ url('../../menu') }}>EIP</a>
        </li>
        <li class="nav-item">
          <a class="nav-link px-5 py-5" href={{ url('../../email.php') }}>Email</a>
        </li>
        <li class="nav-item nav-link-name">
          @if (Auth::guest())
            <a class="nav-link px-5 py-5" href={{ url('../../forum') }}>Login</a>
          @else
            <a class="nav-link px-5 py-5" href="#">{{ Auth::user()->pdepno . " # " . Auth::user()->username_utf8 }}</a>
          @endif  
        </li>
      </ul>
    </div>
  </nav>
  
  @yield('content')

  
  <script>
    $(".dropdown-item").click(function() {  
        $(".navbar-collapse").removeClass("show");      
    });

    function getCurrentUrl(){ // 設定抓取 CurrentUrl (Domain name)
      // console.log(window.location.origin);
      
      /*
      // 測試環境 open
      let thisLocalname = window.location.origin;
      let thisCurrentUrl = thisLocalname.substr(0, 4)=='http' ? thisLocalname : `http://${thisLocalname}`;
      */
      
      // 正式環境 open : 補上路徑 /report
      let thisLocalUrl = thisLocalname.substr(0, 4)=='http' ? thisLocalname : `https://${thisLocalname}`;
      let thisCurrentUrl = `${thisLocalUrl}/report`;
      

      return thisCurrentUrl;
    }

    function alert(){
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '此筆沒有資料...',
        showConfirmButton: false,
        timer: 1500
      })
    }
  </script>

  @yield('reportjs')

</body>
</html>