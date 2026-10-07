<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'OCHOA Laundry')</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap">
<link rel="stylesheet" href="{{ asset('css/ochoa.css') }}">
@stack('head')
</head>
<body>
@auth
    <div class="top">
        <div class="row">
            <div class="brand"><div class="logo">O</div>OCHOA</div>
            <form method="POST" action="{{ route('logout') }}" class="inl">@csrf
                <button class="btn s l">Keluar</button>
            </form>
        </div>
        <div class="nav">
            @foreach (config('ochoa.nav')[auth()->user()->role] as [$label, $route, $patterns])
                <button type="button" class="{{ request()->routeIs(...$patterns) ? 'on' : '' }}" onclick="location.href='{{ route($route) }}'">{{ $label }}</button>
            @endforeach
        </div>
    </div>
    <main>
        @if (session('ok')) <div class="alert ok">{{ session('ok') }}</div> @endif
        @if ($errors->any()) <div class="alert err">@foreach ($errors->all() as $e) {{ $e }}<br> @endforeach</div> @endif
        @yield('content')
    </main>
@else
    @yield('content')
@endauth
@stack('scripts')
</body>
</html>