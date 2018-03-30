
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example-component', require('./components/ExampleComponent.vue'));

// **********************************************
var chart; // Highcharts instance

// This ajax request my by deprecated https://stackoverflow.com/questions/24639335/javascript-console-log-causes-error-synchronous-xmlhttprequest-on-the-main-thr
// This controller request history data from bitfinex and stores in in the DB
$.ajax({
    url : "history/no", // no means initial start or other loads. This action determines on the inital_start flag in the DB
    type : "get",
    async: false, // //async: false. Synchronus request. All other equests will wait until this one is done
    success : function() {},
    error: function(xx) {},
});

// Recalculate price channel
$.ajax({
    url : "pricechannelcalc",// 1 means that the btc_history table will be truncated and fresh historical data will be requeseted, price channel calculated
    type : "get",
    async: false, // //async: false. Synchronus request. All other equests will wait until this one is done
    success : function() { console.log('ajax: price channel recalculated ok'); },
    error: function(xx) {console.log('ajax: price channel recalculated failed');},
});

// When reading and storing historical data is over it is readed from the DB and loaded to the chart
var request = $.get('loaddata'); // Request initiate. Controller call. AJAX request.


// Initial start button click
$('#chart_redraw').click(function () {
    //chart.redraw();
    console.log("chart redraw button clicked");

    // Request history data
    $.ajax({
        url : "history/1",// 1 means that the btc_history table will be truncated and fresh historical data will be requeseted, price channel calculated
        type : "get",
        async: false, // //async: false. Synchronus request. All other equests will wait until this one is done
        success : function() {console.log('ajax: history loaded ok');},
        error: function(xx) {console.log('ajax: history load failed');},
    });

    // Recalculate price channel
    $.ajax({
        url : "pricechannelcalc",// 1 means that the btc_history table will be truncated and fresh historical data will be requeseted, price channel calculated
        type : "get",
        async: false, // //async: false. Synchronus request. All other equests will wait until this one is done
        success : function() { console.log('ajax: price channel recalculated ok'); },
        error: function(xx) {console.log('ajax: price channel recalculated failed');},
    });

    // After fresh historical data was received it is read from DB and outputed to the chart
    var request2 = $.get('loaddata');

    request2.done(function(response) {
        console.log("data loaded from DB ok");
        chart.series[0].setData(response[0],true); // true - redraw the series. Candles
        chart.series[1].setData(response[1],true);// Pricechannel high
        chart.series[2].setData(response[2],true);// Price channel low
        chart.series[3].setData(response[3],true);// Long trade markers
        chart.series[4].setData(response[4],true);// Short trade markers
    });
});



// Update chart data
$('#close_all_positions').click(function () {
    //chart.redraw();
    console.log("update chart data button clicked");

    // After fresh historical data was received it is read from DB and outputed to the chart
    var request2 = $.get('loaddata');

    request2.done(function(response) {
        console.log("data loaded from DB ok v2");
        chart.series[0].setData(response[0],true); // true - redraw the series. Candles
        chart.series[1].setData(response[1],true);// Pricechannel high
        chart.series[2].setData(response[2],true);// Price channel low
        chart.series[3].setData(response[3],true);// Long trade markers
        chart.series[4].setData(response[4],true);// Short trade markers
    });
});





request.done(function(response) { // Ajax request if success

    // Create chart. no animation: http://jsfiddle.net/qk44erj6/
    console.log("chart created");

    chart = new Highcharts.stockChart('container', {

        chart: {
            animation: false,
            renderTo: 'container' // DIV where the chart will be rendered
        },
        yAxis: [{ // Primary yAxis
            title: {
                text: 'price',
                style: {
                    color: 'purple'
                }
            }
        }, { // Secondary yAxis
            title: {
                text: 'profit',
                style: {
                    color: 'green'
                }
            },
            opposite: false
        }],


        series: [{
            name: 'BTCUSD',
            visible: true,
            enableMouseTracking: true,
            type: 'candlestick',
            data: response[0],
            tooltip:
                {
                    valueDecimals: 2, // Quantity of digits .00 in value when hover the cursor over the bar
                    shape: 'square'
                },
            dataGrouping: {
                enabled: false
            }
        },
            {
                name: 'Price channel high',
                visible: true,
                enableMouseTracking: true,
                color: 'red',
                lineWidth: 1,
                data: response[1],
                dataGrouping: {
                    enabled: false
                }

            },
            {
                name: 'Price channel low',
                visible: true,
                enableMouseTracking: true,
                color: 'red',
                lineWidth: 1,
                data: response[2],
                dataGrouping: {
                    enabled: false
                }

            },
            {
                name: 'Long markers',
                visible: true,
                enableMouseTracking: true,
                type: 'scatter',
                color: 'purple',
                //lineWidth: 3,
                data: response[3],
                dataGrouping: {
                    enabled: false
                },
                marker: {
                    fillColor: 'lime',
                    lineColor: 'green',
                    lineWidth: 1,
                    radius: 6,
                    symbol: 'triangle'
                },
            },
            {
                name: 'Short markers',
                visible: true,
                enableMouseTracking: true,
                type: 'scatter',
                //yAxis: 1, // To which of two y axis this series should be linked
                color: 'purple',
                //lineWidth: 3,
                data: response[4],
                dataGrouping: {
                    enabled: false
                },
                marker: {
                    fillColor: 'red',
                    lineColor: 'red',
                    lineWidth: 1,
                    radius: 6,
                    symbol: 'triangle-down'
                },
            }

        ]
    }); // chart


});



// Websocket Laravel echo - VueJS listener
var app = new Vue({
    el: '#app',
    created: function created() {
        Echo.channel('channelDemoEvent').listen('eventTrigger', function (e) {

            // Update last bar on exach even sent from RatchetWebSocket.php
            var last = chart.series[0].data[chart.series[0].data.length - 1];
            last.update({
                //'open': 1000,
                'high': e.update["tradeBarHigh"],
                'low': e.update["tradeBarLow"],
                'close': e.update["tradePrice"]
            }, true);

            // New bar is issued. Flag sent from RatchetWebSocket.php
            if (e.update["flag"]) { // e.update["flag"] = true
                console.log('new bar is added');
                // Add bar to the chart
                chart.series[0].addPoint([e.update["tradeDate"],e.update["tradePrice"],e.update["tradePrice"],e.update["tradePrice"],e.update["tradePrice"]],true, false); // Works good

                // Update price channel
                var request2 = $.get('loaddata');

                request2.done(function(response) {
                    console.log("vue: loading data request worked ok");
                    chart.series[0].setData(response[0],true); // true - redraw the series. Candles
                    chart.series[1].setData(response[1],true);// Pricechannel high
                    chart.series[2].setData(response[2],true);// Price channel low
                });

            }

            // buy flag
            if (e.update["flag"] == "buy") {
                console.log('buy');
                chart.series[3].addPoint([e.update["tradeDate"], e.update["tradePrice"]],true, false); // Works good
            }

            // buy flag
            if (e.update["flag"] == "sell") {
                console.log('buy');
                chart.series[4].addPoint([e.update["tradeDate"], e.update["tradePrice"]],true, false);
            }


            //alert('The event has been triggered! Here is the alert box for proofe!');
            //console.log(e.update);

            //var d = new Date();
            //document.getElementById("demo").innerHTML = d;
            //console.log('hello world: ' + d);
            //document.write('btcusd2: ' + e.update["tradeId"] + '<br>'); // e.update. update is the variable which is defined in event trigger


        });
    }
});

// Button handlers
$('#update').click(function () {
    //chart.series[0].data[3].update(Math.floor(Math.random() * 10));
    //console.log('hello world: ' + (Math.floor(Math.random() * 10)));

    var last = chart.series[0].data[chart.series[0].data.length - 1];
    last.update({
        //'open': 1000,
        //'high': 11500,
        //'low': 8500,
        'close': 9500 + (Math.floor(Math.random() * 800))
    }, true);

});

$('#load_history').click(function () {
    console.log("dddd");
});



// Buy Button
$('#buy_button').click(function () {
    console.log("buy button clicked");

    var request = $.get('placeorder/0.025/buy');
    request.done(function(response) {
        console.log('buy order executed. response: ' + response);

    });
});

// Sell Button
$('#sell_button').click(function () {
    console.log("sell button clicked");

    var request = $.get('placeorder/0.025/sell');
    request.done(function(response) {
        console.log('sell order executed. response: ' + response);

    });
});

