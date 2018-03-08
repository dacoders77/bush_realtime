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


<button id="start_bot">Start bot</button>
<button id="stop_bot">Stop bot</button>
<button id="close_all_positions">Update chart data</button>
<button id="chart_redraw">Initial start</button>
<br>
<button id="buy_button">Buy</button>
<button id="sell_button">Sell</button>
<br>
Bot status: Running
<br><br>

<div id="app"> <!-- VueJS container -->
    <p>This is the event listener page. eventListener.blade.php</p>
</div>

<div id="container" style="width: 100%; height: 500px; border: 1px solid transparent; float: left; text-align: center; display: table-cell; vertical-align: middle">

</div>

<script src="js/app.js" charset="ut8-8"></script>

<script>

</script>


</body>
</html>