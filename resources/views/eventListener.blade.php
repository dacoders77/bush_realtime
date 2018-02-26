<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document</title>

    <script src="http://code.highcharts.com/stock/highstock.js"></script>
    <script src="http://code.highcharts.com/stock/modules/exporting.js"></script>

</head>
<body>

<div id="app">
    <p>This is the event listener page. eventListener.blade.php</p>
</div>

<div id="container" style="width: 100%; height: 500px; border: 1px solid red; float: left; text-align: center; display: table-cell; vertical-align: middle">

</div>


<script src="js/app.js" charset="ut8-8"></script>


</body>
</html>