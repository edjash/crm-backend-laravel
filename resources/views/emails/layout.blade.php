<html>

<head>
    <title>@yield('title')</title>
</head>

<body>
    <p>
        Hi,
    </p>
    @yield('content')
    <p>
        Thanks,
        <br />
        <b>The {{ $appName }} team.</b>
    </p>
</body>

</html>
