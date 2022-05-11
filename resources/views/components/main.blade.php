<html lang="en">
  <!-- HEADER -->
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    {{-- Additional Css files --}}
    @if (is_array($cssFiles))
      @foreach ($cssFiles as $file)
        <link rel="stylesheet" href="{{asset($file)}}">
      @endforeach
    @endif
  </head>

  <!-- BODY -->
  <body>
    {{ $body }}

    <footer>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>

      {{-- Additional Javascript files --}}
      @if (is_array($jsFiles))
        @foreach ($jsFiles as $file)
          <script src="{{asset($file)}}"></script>
        @endforeach
      @endif

    </footer>
  </body>

</html>