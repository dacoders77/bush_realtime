<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {!! env("ASSET_TABLE") !!}:
        {!! DB::table('settings')->where('id', env("SETTING_ID"))->value('symbol') !!}
    </title>

    <script src="http://code.highcharts.com/stock/highstock.js"></script>
    <script src="http://code.highcharts.com/stock/modules/exporting.js"></script>

</head>
<body>
{!! env("ASSET_TABLE") !!}:
{!! DB::table('settings')->where('id', env("SETTING_ID"))->value('symbol') !!}<br>
Net profit:
{!!
DB::table(env("ASSET_TABLE"))
    ->where('id', (DB::table(env("ASSET_TABLE"))->orderBy('time_stamp', 'desc')->first()->id))
    ->value('accumulated_profit');
 !!}<br>

<button id="chart_redraw">Initial start</button>
<br>


<br><br>

<div id="app"> <!-- VueJS container -->
</div>

<div id="container" style="width: 100%; height: 500px; border: 1px solid transparent; float: left; text-align: center; display: table-cell; vertical-align: middle">

</div>

<script src="js/app.js" charset="ut8-8"></script>

<script>

</script>


</body>
</html>